# Order Module Audit Report

**Module:** `app/Domains/Order/`
**Date:** 2026-03-27
**Branch:** `rhjoyOfficial`

---

## Critical Risks

### 1. Idempotency Check Outside Transaction — Duplicate Orders

**File:** `OrderService.php:30-35`
**Impact:** Two simultaneous requests with the same `checkout_token` can both pass the check and create duplicate orders with double stock reservation.

```php
// BEFORE — race condition window between check and transaction
if (!empty($data['checkout_token'])) {
    $existing = Order::where('checkout_token', $data['checkout_token'])->first();
    if ($existing) return $existing;
}
return DB::transaction(function () use ($data) { ... });
```

```php
// AFTER — move inside transaction with pessimistic lock
return DB::transaction(function () use ($data) {
    if (!empty($data['checkout_token'])) {
        $existing = Order::where('checkout_token', $data['checkout_token'])
            ->lockForUpdate()
            ->first();
        if ($existing) return $existing;
    }
    // ... rest of order creation
});
```

---

### 2. No Stock Locking — Overselling Under Concurrency

**File:** `OrderService.php:164-168`
**Impact:** `loadVariantsForItems()` fetches variants without `lockForUpdate()`. Two concurrent checkouts can both see sufficient stock, both reserve, and oversell.

```php
// BEFORE
return ProductVariant::query()
    ->with(['product', 'tierPrices'])
    ->whereIn('id', $variantIds)
    ->get()
    ->keyBy('id');
```

```php
// AFTER
return ProductVariant::query()
    ->with(['product', 'tierPrices'])
    ->whereIn('id', $variantIds)
    ->lockForUpdate()
    ->get()
    ->keyBy('id');
```

---

### 3. Catch Block References Unset Variable — Silent Error Swallowing

**File:** `OrderService.php:44-45, 150`
**Impact:** `$data['items']` is `unset()` on line 45, but the catch block on line 150 calls `count($data['items'] ?? [])`. After a failed transaction, this always logs `item_count: 0`, hiding real debugging info.

```php
// BEFORE (line 44-45)
$items = $data['items'];
unset($data['items']);

// BEFORE (line 150) — always 0 after unset
'item_count' => count($data['items'] ?? []),
```

```php
// AFTER — capture count before unsetting
$itemCount = count($data['items']);
$items = $data['items'];
unset($data['items']);
// ... in catch block:
'item_count' => $itemCount,
```

---

### 4. Grand Total Can Go Negative

**File:** `OrderService.php:126-128`
**Impact:** If `couponDiscount` exceeds `subtotal - discountTotal`, the grand total becomes negative. No floor/clamp applied.

```php
// BEFORE
$grandTotal = ($subtotal - $discountTotal - $couponDiscount) + $shippingCost;
```

```php
// AFTER
$grandTotal = max(0, $subtotal - $discountTotal - $couponDiscount) + $shippingCost;
```

---

### 5. Notification Fires Synchronously Inside Event Constructor

**File:** `OrderStatusChanged.php:32-35`
**Impact:** `Notification::send()` runs inside the constructor, meaning it executes synchronously during the HTTP request. If the notification channel fails or is slow, the status update response is delayed or crashes.

```php
// BEFORE — inside __construct()
Notification::send(
    $order->user,
    new OrderStatusPushNotification($order)
);
```

```php
// AFTER — move to a queued listener
// Remove from constructor entirely.
// Create listener: OrderStatusNotificationListener implements ShouldQueue
// In EventServiceProvider:
// OrderStatusChanged::class => [OrderStatusNotificationListener::class]
```

---

### 6. Mass Assignment Allows Arbitrary Status/Payment Values

**File:** `Order.php:14-33`
**Impact:** `order_status`, `payment_status`, and `payment_method` are in `$fillable`. Since `OrderService::create()` spreads `...$data` into `Order::create()`, any extra fields in validated request data (or future request changes) can override the hardcoded defaults.

```php
// BEFORE — in Order::create([...$data, ...])
// $data may contain order_status, payment_status from request spread

// AFTER — remove sensitive fields from $fillable
protected $fillable = [
    'order_number',
    'user_id',
    'zone_id',
    'subtotal',
    'discount_total',
    'shipping_cost',
    'grand_total',
    'coupon_id',
    'placed_at',
    'confirmed_at',
    'shipped_at',
    'delivered_at',
    'cancelled_at',
    'notes',
    'checkout_token',
    // REMOVED: 'payment_method', 'payment_status', 'order_status'
];
// Set these explicitly in OrderService only
```

---

## High Risks

### 7. OrderItem Missing `variant()` Relationship — releaseStock() Crashes

**File:** `OrderItem.php` (entire file), referenced by `OrderStatusService.php:58`
**Impact:** `releaseStock()` calls `$item->variant` but `OrderItem` has no `variant()` relationship. On cancellation, stock is never released — silently returns `null`.

```php
// BEFORE — OrderItem.php has only order() relationship

// AFTER — add variant relationship
public function variant()
{
    return $this->belongsTo(\App\Domains\Product\Models\ProductVariant::class, 'variant_id');
}
```

---

### 8. Shipping Address Never Saved to order_addresses Table

**File:** `OrderService.php:50-62`
**Impact:** Checkout collects `customer_name`, `customer_phone`, `address_line`, `city` but these are spread into the `orders` table (which has those columns). However, the `order_addresses` table exists with its own model and migration but is never populated. The `Order::shippingAddress()` relationship will always return `null`.

```php
// AFTER — add after order creation (line 62)
$order->shippingAddress()->create([
    'type' => 'shipping',
    'customer_name' => $data['customer_name'],
    'customer_phone' => $data['customer_phone'],
    'address_line' => $data['address_line'],
    'city' => $data['city'],
]);
```

---

### 9. Order Number Collision Risk — Str::random(6)

**File:** `OrderService.php:53`
**Impact:** `strtoupper(Str::random(6))` produces ~2.18 billion combinations but birthday paradox gives 50% collision chance at ~54K orders per day. The `unique` constraint will throw a DB exception, failing the checkout.

```php
// BEFORE
'order_number' => 'BNC-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6)),
```

```php
// AFTER — use longer random or sequential counter
'order_number' => 'BNC-' . now()->format('Ymd') . '-' . strtoupper(Str::random(10)),
// Or use atomic DB sequence / Redis INCR
```

---

### 10. No lockForUpdate on Order During Status Change

**File:** `OrderStatusService.php:23`
**Impact:** Two concurrent admin requests can both read the same `order_status` and apply conflicting transitions. For example, two requests could both see `pending` and one sets `confirmed` while the other sets `cancelled`.

```php
// BEFORE
return DB::transaction(function () use ($order, $newStatus, $oldStatus) {
    $order->order_status = $newStatus->value;
```

```php
// AFTER — re-fetch with lock inside transaction
return DB::transaction(function () use ($order, $newStatus) {
    $order = Order::lockForUpdate()->findOrFail($order->id);
    $oldStatus = $order->order_status;

    if (!$this->isValidTransition($oldStatus, $newStatus->value)) {
        throw new Exception("Invalid status transition from {$oldStatus} to {$newStatus->value}");
    }

    $order->order_status = $newStatus->value;
```

---

### 11. AdminOrderController Eager-Loads Wrong Relation Name

**File:** `AdminOrderController.php:20, 34`
**Impact:** Loads `shippingZone` but `Order` model defines the relationship as `zone()`. The eager load silently returns nothing — admin panel never sees zone data.

```php
// BEFORE
->with(['items', 'coupon', 'shippingZone'])

// AFTER
->with(['items', 'coupon', 'zone'])
```

---

### 12. Exception Message Leaked to Client

**File:** `CheckoutController.php:42-43`
**Impact:** `$e->getMessage()` is passed as the primary error message to the API response. Internal exceptions (DB errors, class names, SQL) are exposed to the client.

```php
// BEFORE
return ApiResponse::error(
    $e->getMessage() ?: 'Order failed',
    config('app.debug') ? $e->getMessage() : null,
    $this->resolveStatus($e),
);
```

```php
// AFTER
return ApiResponse::error(
    'Order could not be placed. Please try again.',
    config('app.debug') ? $e->getMessage() : null,
    $this->resolveStatus($e),
);
```

---

## Medium Risks

### 13. `isLateToShip()` Crashes on Null `placed_at`

**File:** `Order.php:90-95`
**Impact:** If `placed_at` is `null`, calling `->addHours(48)` throws `Call to a member function on null`.

```php
// BEFORE
public function isLateToShip(): bool
{
    if ($this->shipped_at) return false;
    return $this->placed_at->addHours(48)->isPast();
}
```

```php
// AFTER
public function isLateToShip(): bool
{
    if ($this->shipped_at || !$this->placed_at) return false;
    return $this->placed_at->addHours(48)->isPast();
}
```

---

### 14. `Processing` Status Missing From Timestamp Match Block

**File:** `OrderStatusService.php:27-33`
**Impact:** When order moves to `processing`, no timestamp is recorded. If you later need to track processing time, there's no data.

```php
// BEFORE
match ($newStatus) {
    OrderStatus::Confirmed => $order->confirmed_at = now(),
    OrderStatus::Shipped   => $order->shipped_at = now(),
    OrderStatus::Delivered => $order->delivered_at = now(),
    OrderStatus::Cancelled => $order->cancelled_at = now(),
    default => null
};
```

```php
// AFTER — add processing_at column via migration, then:
match ($newStatus) {
    OrderStatus::Confirmed  => $order->confirmed_at = now(),
    OrderStatus::Processing => $order->processing_at = now(),
    OrderStatus::Shipped    => $order->shipped_at = now(),
    OrderStatus::Delivered  => $order->delivered_at = now(),
    OrderStatus::Cancelled  => $order->cancelled_at = now(),
    default => null
};
```

---

### 15. No CouponUsage Record Created — Per-User Limit Unenforceable

**File:** `OrderService.php:114-120`
**Impact:** `used_count` is atomically incremented, but no `coupon_usages` record is created linking the coupon to the user. Per-user usage limits cannot be enforced.

```php
// AFTER — add after increment
CouponUsage::create([
    'coupon_id' => $coupon->id,
    'user_id'   => Auth::id(),
    'order_id'  => $order->id,
]);
```

---

### 16. CheckoutRequest Missing Field Length Limits

**File:** `CheckoutRequest.php:17-18`
**Impact:** `customer_name` and `customer_phone` have no `max` rule. Malicious input can exceed DB column size (default `VARCHAR(255)`) causing truncation or errors.

```php
// BEFORE
'customer_name' => 'required|string',
'customer_phone' => 'required|string',
```

```php
// AFTER
'customer_name' => 'required|string|max:255',
'customer_phone' => 'required|string|max:20',
'address_line' => 'required|string|max:500',
'city' => 'required|string|max:100',
```

---

### 17. OrderResource Missing Key Data

**File:** `OrderResource.php:9-37`
**Impact:** Response omits `customer_name`, `customer_phone`, shipping address, coupon details, and `payment_method`. Frontend cannot display order confirmation details.

```php
// AFTER — add to toArray()
'customer_name' => $this->customer_name,
'customer_phone' => $this->customer_phone,
'payment_method' => $this->payment_method,
'coupon' => $this->whenLoaded('coupon', fn() => [
    'code' => $this->coupon->code,
    'discount' => (float) $this->discount_total,
]),
'shipping_address' => $this->whenLoaded('shippingAddress'),
```

---

### 18. Duplicate Trait Use in OrderCreated Event

**File:** `OrderCreated.php:16-21`
**Impact:** `Dispatchable` and `SerializesModels` are used twice. `InteractsWithSockets` is used on line 16 but the second `use` on line 21 overwrites it. Not a runtime error but indicates copy-paste sloppiness that may cause confusion.

```php
// BEFORE
use Dispatchable, InteractsWithSockets, SerializesModels; // line 16
use Dispatchable, SerializesModels; // line 21

// AFTER — keep only one
use Dispatchable, InteractsWithSockets, SerializesModels;
```

---

### 19. AdminOrderController show() Returns Raw Model Instead of Resource

**File:** `AdminOrderController.php:33-34`
**Impact:** `show()` returns the raw Eloquent model via `ApiResponse::success()`, while other endpoints presumably use `OrderResource`. Inconsistent API shape between list and detail views.

```php
// BEFORE
return ApiResponse::success(
    $order->load(['items', 'coupon', 'shippingZone']),

// AFTER
return ApiResponse::success(
    new OrderResource($order->load(['items', 'coupon', 'zone'])),
```

---

### 20. Dead Code — Empty Action/Event/Listener Files

**Files:**
- `app/Domains/Order/Actions/CreateOrderAction.php`
- `app/Domains/Order/Actions/ConfirmOrderAction.php`
- `app/Domains/Order/Actions/ShipOrderAction.php`
- `app/Domains/Order/Events/OrderPlaced.php`
- `app/Domains/Order/Events/OrderDelivered.php`
- `app/Domains/Order/Listeners/` (empty listeners)
- `app/Domains/Order/Services/OrderCalculationService.php`

**Impact:** Empty placeholder files add confusion and suggest incomplete refactoring. Should either be implemented or removed.

---

## Summary

| Severity | Count | Key Themes |
|----------|-------|------------|
| Critical | 6 | Race conditions, negative totals, mass assignment, sync notifications |
| High | 6 | Missing relationship, lost address data, collision risk, wrong relation name |
| Medium | 8 | Null safety, missing validation, incomplete resource, dead code |
| **Total** | **20** | |

**Top 3 Priorities:**
1. Move idempotency check + variant loading inside transaction with `lockForUpdate()` (Issues #1, #2)
2. Add `variant()` relationship to `OrderItem` so cancellation actually releases stock (Issue #7)
3. Remove `order_status`/`payment_status`/`payment_method` from `$fillable` to prevent mass assignment (Issue #6)
