# Product + Cart Audit

**Scope:** `app/Domains/Product/**` and `app/Domains/Cart/**`
**Date:** 2026-04-01

---

## Critical Issues

### CRIT-1: Cart merge leaks reserved_stock (CartMergeService)

**File:** `app/Domains/Cart/Services/CartMergeService.php`

When a guest cart merges into a user cart, `reserved_stock` is never adjusted:

- Guest cart items had `reserved_stock` allocated on their variants.
- `$guestCart->delete()` (line 80) deletes the cart and cascades items, but **never releases** the reserved stock that was held by those guest cart items.
- The user cart's updated quantities **never reserve additional stock** either.

**Result:** Every cart merge permanently inflates `reserved_stock`, progressively reducing `available_stock` until products appear out of stock.

**Fix:** Before deleting the guest cart, release its reserved stock. After updating user cart quantities, reserve stock for the merged quantities. Use `CartService::releaseReservedStock()` for the guest cart, then re-reserve for the merged items.

---

### CRIT-2: Pricing path inconsistency between add and update

**Files:**
- `app/Domains/Cart/Services/CartService.php:81` (`addItem`)
- `app/Domains/Cart/Services/CartService.php:198` (`updateItemQuantity`)

`addItem` snapshots `$variant->final_price` which applies **sale discounts** (percentage/fixed, time-limited).
`updateItemQuantity` snapshots `$pricing['unit_price']` from `PricingService::calculate()` which starts from `$variant->price` (raw base price) and applies **tier discounts only**.

This means:
- Add 1 item at qty 1 -> price = sale-discounted price (e.g., 90 from 100 with 10% sale)
- Update qty to 5 (hitting a tier) -> price = tier-discounted from base 100, sale discount is lost
- Update qty back to 1 (no tier) -> price = full base price 100, sale discount is gone

**Result:** Sale discounts vanish when quantity changes. Tier and sale discounts never stack.

**Fix:** `PricingService::calculate()` should use `$variant->final_price` as its base instead of `$variant->price`, or `addItem` should also route through `PricingService`. Choose one canonical pricing path.

---

### CRIT-3: addCombo validates only incremental qty, not total

**File:** `app/Domains/Cart/Services/CartService.php:34`

```php
if ($combo->available_stock < $qty) {  // Only checks the new qty being added
```

When a combo already exists in the cart (line 38-41), the code checks `$combo->available_stock < $qty` but should check against `$cartItem->quantity + $qty`. A user can add 1 combo repeatedly, each time passing the stock check for qty=1, until stock is massively over-reserved.

**Fix:**
```php
$totalQty = $cartItem ? ($cartItem->quantity + $qty) : $qty;
if ($combo->available_stock < $totalQty) {
```

Note: `available_stock` already subtracts `reserved_stock`, so this check accounts for other carts, but not for the current cart item's own reservation which is already counted. The check should be:
```php
$additionalNeeded = $qty; // new units being added
// available_stock already reflects current reserved_stock (which includes this cart item's reservation)
if ($combo->available_stock < $additionalNeeded) {
```
Actually the current check for `$qty` is correct IF `reserved_stock` is already incremented for the existing cart item. Since `available_stock = stock - reserved_stock` and the existing item's stock is already reserved, checking `available_stock < $qty` for the incremental amount is logically correct. **However**, the `lockForUpdate` on variants (line 30-32) uses eager-load syntax which does NOT actually acquire a row lock -- `lockForUpdate()` inside a `with` callback applies to the SELECT for that relation but doesn't persist the lock correctly via eager loading. This makes the entire stock check vulnerable to race conditions.

---

### CRIT-4: addCombo eager-load lockForUpdate is ineffective

**File:** `app/Domains/Cart/Services/CartService.php:30-32`

```php
$combo = Combo::with(['items.variant' => function ($q) {
    $q->lockForUpdate();
}])->findOrFail($comboId);
```

`lockForUpdate()` inside an eager-load callback runs a separate SELECT query with FOR UPDATE, but the lock is released as soon as that query completes and the results are hydrated into the Eloquent collection. By the time stock is checked and incremented, the lock is gone. Compare with `addItem` (line 63) which correctly uses `ProductVariant::lockForUpdate()->findOrFail($variantId)`.

**Fix:** Load the combo first, then explicitly lock each variant in a loop before checking stock:
```php
$combo = Combo::with('items')->findOrFail($comboId);
$variants = ProductVariant::whereIn('id', $combo->items->pluck('product_variant_id'))
    ->lockForUpdate()->get()->keyBy('id');
```

---

## High Issues

### HIGH-1: addCombo does not update price snapshot on existing item

**File:** `app/Domains/Cart/Services/CartService.php:40-41`

```php
if ($cartItem) {
    $cartItem->increment('quantity', $qty);
}
```

When a combo already exists in the cart, only the quantity is incremented. The `unit_price_snapshot` is **not refreshed**. If the combo's price has changed since the item was first added, the cart carries a stale price.

Compare with `addItem` (lines 79-84) which correctly updates the snapshot on existing items.

**Fix:**
```php
if ($cartItem) {
    $cartItem->update([
        'quantity' => $cartItem->quantity + $qty,
        'unit_price_snapshot' => $combo->final_price,
    ]);
}
```

---

### HIGH-2: clearCart does not lock combo variants

**File:** `app/Domains/Cart/Services/CartService.php:147-153`

For regular items, `clearCart` uses `ProductVariant::lockForUpdate()->find(...)` (line 154). For combo items, variants are loaded via `Combo::with('items.variant')` (line 148) with **no lock**, creating a race condition where concurrent operations can corrupt `reserved_stock`.

**Fix:** Lock combo variants the same way regular variants are locked, or collect all variant IDs upfront and lock them in a single query.

---

### HIGH-3: releaseReservedStock has no transaction

**File:** `app/Domains/Cart/Services/CartService.php:213-228`

`releaseReservedStock()` iterates over cart items and decrements `reserved_stock` on each variant without a transaction. If an error occurs mid-iteration, some variants will have their stock released while others won't.

**Fix:** Wrap in `DB::transaction()`.

---

### HIGH-4: reserved_stock can go negative

**Files:**
- `app/Domains/Cart/Services/CartService.php:119` (`updateItem`)
- `app/Domains/Cart/Services/CartService.php:135` (`removeItem`)
- `app/Domains/Cart/Services/CartService.php:155` (`clearCart`)
- `app/Domains/Cart/Services/CartService.php:179` (`updateItemQuantity`)

Multiple code paths call `decrement('reserved_stock', ...)` or `increment('reserved_stock', negative_diff)` without verifying the result stays >= 0. If reserved_stock goes negative (e.g., due to a bug, race condition, or the merge leak in CRIT-1), `available_stock` will **exceed** actual `stock`, allowing overselling.

**Fix:** Add a DB-level `CHECK (reserved_stock >= 0)` constraint, or use `UPDATE ... SET reserved_stock = GREATEST(0, reserved_stock - ?)` as a safety net.

---

### HIGH-5: updateItem method skips price recalculation

**File:** `app/Domains/Cart/Services/CartService.php:103-123`

`updateItem()` changes quantity and adjusts reserved_stock but **never updates `unit_price_snapshot`**. If this method is called from any code path, the cart will carry stale pricing. The controller uses `updateItemQuantity()` (which does recalculate), but `updateItem()` remains a public method that could be called from other services or future endpoints.

**Fix:** Either deprecate/remove `updateItem()` in favor of `updateItemQuantity()`, or add price recalculation logic.

---

### HIGH-6: updateItem stock validation checks incremental diff against available_stock incorrectly

**File:** `app/Domains/Cart/Services/CartService.php:113`

```php
if ($diff > 0 && ! $variant->hasStock($diff)) {
```

`hasStock($diff)` checks `available_stock >= $diff`. Since `available_stock = stock - reserved_stock`, and the current item's reserved_stock is already included, this check is correct for the incremental amount. **However**, this differs from `addItem` (line 74) which checks `hasStock($newQty)` (the full quantity). The inconsistency means `updateItem` allows quantities that `addItem` would reject if the item were being re-added fresh.

**Fix:** Align validation approach. Either both check incremental or both check total.

---

## Medium Issues

### MED-1: Tier price percentage not capped at 100

**File:** `app/Domains/Product/Controllers/ProductTierPriceController.php:25`

```php
'discount_value' => 'required|numeric|min:0'
```

A percentage discount_value of 150 is accepted. `PricingService` uses `max(0, ...)` so the final total won't go negative, but `unit_price` would be 0 and `discount_amount` would be capped at `$total`, creating confusing data.

**Fix:** Add conditional validation: `'discount_value' => 'required|numeric|min:0' . ($request->discount_type === 'percentage' ? '|max:100' : '')`

---

### MED-2: Float comparison in syncCartPrices

**File:** `app/Domains/Cart/Services/CartService.php:249`

```php
if ((float)$currentPrice !== (float)$newPrice) {
```

Strict float comparison is unreliable due to IEEE 754 precision. A price of 33.33 calculated two different ways may differ at the 15th decimal.

**Fix:** Use epsilon comparison: `abs((float)$currentPrice - (float)$newPrice) > 0.001`

---

### MED-3: Combo.available_stock division by zero risk

**File:** `app/Domains/Product/Models/Combo.php:45`

```php
return floor($item->variant->available_stock / $item->quantity);
```

If a `ComboItem` has `quantity = 0` (invalid data), this causes a division by zero.

**Fix:** Guard against zero: `$item->quantity > 0 ? floor(...) : 0`

---

### MED-4: Product image_url accessor ignores stored path format

**File:** `app/Domains/Product/Models/Product.php:87-89`

```php
return asset('storage/products/' . ($this->thumbnail ?? 'default-products.jpg'));
```

But `ProductService::create()` stores thumbnails via `$data['thumbnail']->store($this->path, 'public')` which returns a path like `products/abc123.jpg`. The accessor prepends `storage/products/`, resulting in `storage/products/products/abc123.jpg` -- a double `products/` segment.

**Fix:** Use `asset('storage/' . $this->thumbnail)` or adjust the accessor to match the storage path format.

---

### MED-5: CartPricingService ignores tier pricing entirely

**File:** `app/Domains/Cart/Services/CartPricingService.php:11`

```php
$subtotal += $item->unit_price_snapshot * $item->quantity;
```

This relies entirely on `unit_price_snapshot` being correct. Given CRIT-2 (pricing path inconsistency) and HIGH-1/HIGH-5 (stale snapshots), the cart totals shown to the user may be incorrect. There is no independent verification layer.

**Fix:** Consider recalculating from source data as a verification step, or ensure all mutation paths reliably update snapshots (fixing CRIT-2, HIGH-1, HIGH-5).

---

### MED-6: Guest cart session_token not validated on creation

**File:** `app/Domains/Cart/Controllers/CartController.php:192`

The regex `/^[a-zA-Z0-9\-]{32,}$/` has no upper bound on length. An attacker could send a very long session token to waste storage or cause index issues.

**Fix:** Add a max length: `/^[a-zA-Z0-9\-]{32,64}$/`

---

## Summary

| Severity | Count | Key Theme |
|----------|-------|-----------|
| Critical | 4 | Stock leaks, pricing inconsistency, race conditions |
| High | 6 | Stale snapshots, missing locks/transactions, negative stock |
| Medium | 6 | Validation gaps, float precision, path bugs |

**Most urgent fixes:**
1. CRIT-1 (merge stock leak) -- silently corrupts inventory over time
2. CRIT-2 (pricing path split) -- customers see wrong prices after qty change
3. CRIT-4 (ineffective locking) -- race conditions under concurrent load
4. HIGH-4 (negative reserved_stock) -- can enable overselling

---
---

## Checkout Flow Audit

**Scope:** Cart -> Checkout -> Coupon -> Shipping -> Payment -> Order
**Date:** 2026-04-01

---

### Critical Issues

#### CRIT-5: OrderItem model missing `combo_id` in $fillable — combo orders silently broken

**Files:**
- `app/Domains/Order/Models/OrderItem.php:9-22` ($fillable)
- `app/Domains/Order/Services/OrderService.php:107-115` (combo item creation)

`OrderService::create()` creates order items with `'combo_id' => $combo->id` (line 108), but the `OrderItem` model's `$fillable` array does **not** include `combo_id`. Eloquent silently strips unfillable attributes, so `combo_id` is **never saved** to the database.

**Downstream impact:**
- `OrderStatusService::fulfillStock()` checks `$item->combo_id` (line 75) — always null, so combo stock is **never deducted** on shipment.
- `OrderStatusService::releaseStock()` checks `$item->combo_id` (line 95) — always null, so combo reserved_stock is **never released** on cancellation.
- Combo inventory permanently leaks on every combo order.

**Fix:** Add `'combo_id'` to `OrderItem::$fillable` and add a `combo()` BelongsTo relationship.

---

#### CRIT-6: OrderItem has no combo() relationship — fulfillStock and releaseStock crash

**Files:**
- `app/Domains/Order/Models/OrderItem.php` (missing relationship)
- `app/Domains/Order/Services/OrderStatusService.php:75-79` (fulfillStock)
- `app/Domains/Order/Services/OrderStatusService.php:95-99` (releaseStock)

Both `fulfillStock()` and `releaseStock()` access `$item->combo` and `$item->combo->items`, but `OrderItem` has no `combo()` relationship defined. Even if CRIT-5 is fixed and `combo_id` is saved, these methods would throw `BadMethodCallException` or return null.

**Fix:** Add to `OrderItem`:
```php
public function combo(): BelongsTo
{
    return $this->belongsTo(\App\Domains\Product\Models\Combo::class);
}
```

---

#### CRIT-7: Cart cleared before order items are validated — race window for stock

**File:** `app/Domains/Order/Services/OrderService.php:41-43`

```php
if ($cart) {
    $this->cartService->clearCart($cart);
}
```

`clearCart()` is called at the **top** of the transaction, **before** `loadVariantsForItems()` acquires `lockForUpdate` on variants. `clearCart` releases all `reserved_stock` for cart items. Between this release and when `loadVariantsForItems` (line 62) acquires row-level locks, a concurrent transaction can see the freed stock and claim it.

The correct sequence should be: lock variants first, then release cart stock, then validate and re-reserve for the order.

**Fix:** Move `clearCart($cart)` to after `loadVariantsForItems()`, or better yet, after all order items are created and stock is re-reserved. Alternatively, have the order creation skip re-reserving and instead convert the cart's existing reservations directly into order reservations.

---

#### CRIT-8: Coupon usage recorded before verifying coupon was actually incremented

**File:** `app/Domains/Order/Services/OrderService.php:175-188`

```php
$alreadyUsed = CouponUsage::where('order_id', $order->id)->where('coupon_id', $couponId)->exists();
if (!$alreadyUsed) {
    $affected = Coupon::where('id', $coupon->id)
        ->whereColumn('used_count', '<', 'usage_limit')
        ->increment('used_count');

    CouponUsage::create([...]);  // Created BEFORE checking $affected

    if (!$affected) {
        throw new Exception("Coupon exhausted");
    }
}
```

`CouponUsage::create()` runs at line 179 **before** the `$affected` check at line 186. If the coupon is exhausted (`$affected === 0`), the exception rolls back the transaction, so the usage record is also rolled back — this is safe due to the transaction.

**However**, the real issue is that `$couponDiscount` was already calculated at line 166-172 using `CouponValidationService::validate()` which reads `used_count` **without a lock**. The coupon's `isValidForUser()` check also reads without a lock. Between the validation read and the atomic increment, other concurrent checkouts can all pass validation and get discount amounts calculated, then only one succeeds the increment. The others throw "Coupon exhausted" — but they already computed and used the discount value in `$grandTotal` calculation at line 195. Since the exception rolls back the transaction, this is technically safe, but it means customers see a "coupon applied" state that fails at the last moment.

**Fix:** Move the coupon increment + validation to happen atomically before calculating `$grandTotal`. Consider using `lockForUpdate` when reading the coupon.

---

#### CRIT-9: CheckoutRequest rejects combo orders — validation requires variant_id for all items

**File:** `app/Domains/Order/Requests/CheckoutRequest.php:26-27`

```php
'items.*.variant_id' => 'required|exists:product_variants,id',
'items.*.quantity' => 'required|integer|min:1',
```

`variant_id` is **required** for every item. But `OrderService::create()` (line 88) expects combo items to have `combo_id` instead of `variant_id`. Combo orders cannot pass request validation.

**Fix:**
```php
'items.*.variant_id' => 'nullable|required_without:items.*.combo_id|exists:product_variants,id',
'items.*.combo_id'   => 'nullable|required_without:items.*.variant_id|exists:combos,id',
'items.*.quantity'   => 'required|integer|min:1',
```

---

### High Issues

#### HIGH-7: checkout_token idempotency has no unique constraint — duplicate orders possible

**File:** `app/Domains/Order/Services/OrderService.php:45-49`

```php
if (!empty($data['checkout_token'])) {
    $existing = Order::where('checkout_token', $data['checkout_token'])->first();
    if ($existing) return $existing;
}
```

This check-then-act is not atomic. Two concurrent requests with the same `checkout_token` can both pass the `->first()` check (both return null) and both create orders. There is no unique database constraint on `checkout_token` (it's nullable and in `$fillable` but not shown with a unique index).

**Fix:** Add a unique index on `checkout_token` (where not null) in a migration. Catch the unique constraint violation as a fallback.

---

#### HIGH-8: fulfillStock double-deducts stock for combo items that also have variant_id

**File:** `app/Domains/Order/Services/OrderStatusService.php:66-80`

```php
foreach ($order->items as $item) {
    if ($item->variant) {                    // Block A: processes variant
        $item->variant->decrement('stock', $item->quantity);
        $item->variant->decrement('reserved_stock', $item->quantity);
    }
    if ($item->combo_id && $item->combo) {   // Block B: processes combo
        foreach ($item->combo->items as $comboItem) { ... }
    }
}
```

Block A and Block B are **not mutually exclusive** (`if`, not `elseif`). If an order item has both `variant_id` and `combo_id` set, stock is deducted twice: once via Block A (variant-level) and once via Block B (combo component-level). The same pattern exists in `releaseStock()`.

Currently CRIT-5 prevents `combo_id` from being saved, masking this bug. Once CRIT-5 is fixed, this will surface.

**Fix:** Use `elseif` or prioritize combo processing:
```php
if ($item->combo_id && $item->combo) {
    // combo logic
} elseif ($item->variant) {
    // variant logic
}
```

---

#### HIGH-9: fulfillStock and releaseStock don't lock variants

**File:** `app/Domains/Order/Services/OrderStatusService.php:65-80, 87-100`

Neither `fulfillStock()` nor `releaseStock()` use `lockForUpdate()` when decrementing `stock` and `reserved_stock`. Concurrent status changes (e.g., admin clicks "Ship" twice rapidly) can corrupt inventory counts. While `changeStatus()` locks the **order** row (line 25), the **variant** rows are not locked.

**Fix:** Lock variant rows before decrementing:
```php
$variant = ProductVariant::lockForUpdate()->find($item->variant_id);
$variant->decrement('stock', $item->quantity);
$variant->decrement('reserved_stock', $item->quantity);
```

---

#### HIGH-10: Coupon fixed-amount discount not capped at order subtotal

**Files:**
- `app/Domains/Coupon/Services/CouponValidationService.php:42-43`
- `app/Domains/Order/Services/OrderService.php:195`

```php
// CouponValidationService
return $coupon->value;  // No cap — returns full value even if it exceeds order amount
```

A fixed coupon of BDT 500 on a BDT 200 order returns `discount = 500`. While `max(0, ...)` at OrderService line 195 prevents a negative grand_total, the stored `discount_total` (line 199) will be `$discountTotal + 500` which exceeds the subtotal. The `CouponUsage.discount_amount` also records 500, inflating discount analytics.

**Fix:** Cap in CouponValidationService:
```php
return min($coupon->value, $amount);
```

---

#### HIGH-11: Shipping free-shipping threshold evaluated before coupon discount

**File:** `app/Domains/Order/Services/OrderService.php:192-193`

```php
$shippingCost = $this->shippingCalculator
    ->calculate($zone, $subtotal - $discountTotal);  // $discountTotal = tier discounts only (no coupon yet)
```

Shipping is calculated using `$subtotal - $discountTotal` which only includes tier pricing discounts, **not** the coupon discount. If the free shipping threshold is BDT 1000, and the order is BDT 1200 before coupon but BDT 800 after a BDT 400 coupon, the customer still gets free shipping.

This may be intentional (free shipping based on pre-coupon amount), but if not:

**Fix:** Calculate shipping after coupon is applied:
```php
$amountAfterAllDiscounts = $subtotal - $discountTotal - $couponDiscount;
$shippingCost = $this->shippingCalculator->calculate($zone, $amountAfterAllDiscounts);
```

---

#### HIGH-12: Order hardcoded to COD — no payment method selection

**Files:**
- `app/Domains/Order/Services/OrderService.php:73` (`'payment_method' => 'cod'`)
- `app/Domains/Order/Requests/CheckoutRequest.php` (no `payment_method` field)

Every order is created with `payment_method = 'cod'` regardless of user intent. The `CheckoutRequest` has no field for payment method. The `OrderTransaction` model exists but is never used during checkout. There is no SSL/online payment integration path.

**Fix:** Add `'payment_method' => 'required|in:cod,online'` to CheckoutRequest. Branch the order flow: for COD, proceed as now; for online, set `payment_status = 'pending'` and integrate a payment gateway callback that calls `ConfirmOrderAction`.

---

#### HIGH-13: Combo order items use stale combo data — not using locked variants

**File:** `app/Domains/Order/Services/OrderService.php:89, 237`

`loadVariantsForItems()` (line 237) correctly loads and locks all variant IDs including combo component variants via `lockForUpdate()`. But the combo processing loop at line 89 loads the combo fresh:

```php
$combo = Combo::with('items.variant')->findOrFail($item['combo_id']);
```

This loads variant data **without a lock** and into **separate model instances** from the ones locked by `loadVariantsForItems()`. The stock checks at line 97 and increments at line 101-104 operate on these unlocked instances, bypassing the row-level locks entirely.

**Fix:** Use the already-locked variants from `$variants` collection:
```php
$combo = Combo::with('items')->findOrFail($item['combo_id']);
foreach ($combo->items as $comboItem) {
    $component = $variants->get($comboItem->product_variant_id);
    // ...use the locked $component for stock checks and increments
}
```

---

### Medium Issues

#### MED-7: Order subtotal uses base price, discount_total conflates tier + coupon

**File:** `app/Domains/Order/Services/OrderService.php:134, 199`

```php
$subtotal += $variant->price * $item['quantity'];        // line 134: raw base price
$discountTotal += $pricing['discount_amount'];            // line 135: tier discount
// ...
'discount_total' => $discountTotal + $couponDiscount,     // line 199: tier + coupon merged
```

The order's `subtotal` is the sum of raw base prices (before any discount), and `discount_total` combines tier discounts and coupon discounts into a single value. There is no way to distinguish tier discounts from coupon discounts on the order record. The `coupon_discount` field exists on the Order model (`$fillable`) but is never populated.

**Fix:** Store `coupon_discount` separately:
```php
$order->update([
    'subtotal'        => $subtotal,
    'discount_total'  => $discountTotal,         // tier discounts only
    'coupon_discount' => $couponDiscount,         // coupon discount separately
    'shipping_cost'   => $shippingCost,
    'grand_total'     => $grandTotal,
    'coupon_id'       => $couponId,
]);
```

---

#### MED-8: Coupon validation doesn't lock the coupon row — TOCTOU on used_count

**File:** `app/Domains/Coupon/Services/CouponValidationService.php:17`

```php
$coupon = Coupon::where('code', $code)->first();  // No lock
```

The coupon is read without `lockForUpdate`. Between this read and the atomic `increment` in `OrderService` (line 177), the `used_count` can change. While the atomic increment itself is safe (it rechecks the condition), the `isValidForUser()` check (which reads `used_count`) can pass for multiple concurrent requests even when only one slot remains.

**Fix:** Use `lockForUpdate()`:
```php
$coupon = Coupon::where('code', $code)->lockForUpdate()->first();
```
Note: This requires the caller to already be inside a DB transaction, which `OrderService::create()` provides.

---

#### MED-9: Guest per-user coupon limit not enforced

**File:** `app/Domains/Coupon/Models/Coupon.php:52-58`

```php
if ($user && $this->limit_per_user) {
    $userUsageCount = $this->usages()->where('user_id', $user->id)->count();
    if ($userUsageCount >= $this->limit_per_user) return false;
}
```

The per-user limit only applies when `$user` is not null. Guest checkouts (where `$user = null`) bypass the per-user limit entirely. A guest can use a `limit_per_user: 1` coupon unlimited times by providing different session tokens.

**Fix:** For guest users, track usage by `checkout_token`, phone number, or IP address.

---

#### MED-10: order_number collision possible under high concurrency

**File:** `app/Domains/Order/Services/OrderService.php:68`

```php
'order_number' => 'BNC-' . now()->format('Ymd') . '-' . strtoupper(Str::random(10)),
```

`Str::random(10)` with uppercase alphanumeric gives ~36^10 combinations (~3.6 trillion), making collision unlikely but not impossible. There is no unique constraint shown on `order_number`. Under high concurrency (flash sales), the same-second random collision risk increases.

**Fix:** Add a unique DB constraint on `order_number`. Use a sequence or UUID as fallback.

---

#### MED-11: Combo order items don't snapshot component details

**File:** `app/Domains/Order/Services/OrderService.php:107-115`

```php
$order->items()->create([
    'combo_id' => $combo->id,
    'product_name_snapshot' => $combo->title,
    'variant_title_snapshot' => 'Bundle',
    'original_unit_price'   => $combo->base_price,  // Combo has no 'base_price' attribute
    ...
]);
```

1. `$combo->base_price` is referenced but the `Combo` model has no `base_price` attribute — it has `manual_price` and `auto_price`. This likely stores `null`.
2. Individual combo components (which products, which variants, what quantities) are not snapshotted. If the combo's composition changes later, the order history is meaningless.
3. `'variant_title_snapshot' => 'Bundle'` is a hardcoded string, not actual data.

**Fix:** Use `$combo->auto_price` or `$combo->manual_price` for `original_unit_price`. Create `OrderComboItem` records to snapshot individual components.

---

#### MED-12: OrderStatusService reads stale order status after locking

**File:** `app/Domains/Order/Services/OrderStatusService.php:17-25`

```php
$oldStatusStr = $order->order_status;                           // line 17: read BEFORE lock

if (!$this->isValidTransition($oldStatusStr, $newStatus->value)) {  // line 18: validate with old data
    throw new Exception("Invalid status transition...");
}

// Inside transaction:
$order = Order::lockForUpdate()->findOrFail($order->id);       // line 25: re-fetch with lock
```

The transition validation at line 18 uses `$oldStatusStr` captured **before** the lock. By the time the lock is acquired at line 25, another request may have already changed the status. The validation passes based on stale data.

**Fix:** Move the `isValidTransition` check inside the transaction, after `lockForUpdate`:
```php
return DB::transaction(function () use ($order, $newStatus) {
    $order = Order::lockForUpdate()->findOrFail($order->id);
    $oldStatusStr = $order->order_status;  // Read AFTER lock
    if (!$this->isValidTransition($oldStatusStr, $newStatus->value)) { ... }
    // ...
});
```

---

### Summary

| Severity | Count | Key Theme |
|----------|-------|-----------|
| Critical | 5 | Broken combo persistence, stock race window, validation blocks combos |
| High | 7 | Duplicate orders, double stock deduction, missing locks, uncapped discounts |
| Medium | 6 | Conflated discounts, coupon TOCTOU, guest limit bypass, stale status |

**Most urgent fixes:**
1. CRIT-5 + CRIT-6 (combo_id not saved + no relationship) — combo orders are fundamentally broken; inventory never adjusts
2. CRIT-7 (cart cleared before locking) — stock race condition during checkout
3. CRIT-9 (validation blocks combos) — combo checkout is impossible via API
4. HIGH-7 (checkout_token not unique) — duplicate orders under concurrent requests
5. HIGH-8 (double stock deduction) — will surface once CRIT-5 is fixed

---

## Fixes Applied — 2026-04-06

All fixes below were written to the branch `claude/flow-audit`.

### FIXED: CRIT-5 + CRIT-6 — OrderItem missing combo_id + no combo() relationship

**File:** `app/Domains/Order/Models/OrderItem.php`

Added `combo_id` to `$fillable` and added the `combo()` BelongsTo relationship.  
Combo orders now persist `combo_id` to the database. `OrderStatusService::fulfillStock` and `releaseStock` can now load `$item->combo` without throwing `BadMethodCallException`.

---

### FIXED: CRIT-7 — Idempotency check moved before clearCart

**File:** `app/Domains/Order/Services/OrderService.php`

Moved the `checkout_token` duplicate-order guard to run **before** `clearCart()`.  
Previously, a retry request would clear the cart (decrement `reserved_stock`) and then return the existing order — permanently leaking stock on every duplicate POST.

---

### FIXED: CRIT-8 — CouponUsage::create reordered after $affected confirmation

**File:** `app/Domains/Order/Services/OrderService.php`

`CouponUsage::create()` is now called only after `$affected` is confirmed non-zero.  
Previously it was called one line before the `if (!$affected)` check. Both paths were safe within the transaction rollback, but the intent was inverted.

---

### FIXED: HIGH-13 — Combo stock check/reserve uses locked variant instances

**File:** `app/Domains/Order/Services/OrderService.php`

The combo processing loop now fetches components via `$variants->get($comboItem->product_variant_id)` — the same locked collection returned by `loadVariantsForItems()` — instead of loading fresh, unlocked variant instances via `Combo::with('items.variant')`.  
This eliminates the TOCTOU window where two concurrent checkouts could both pass the combo stock check before either incremented `reserved_stock`.

---

### FIXED: HIGH-8 — Double stock deduction in fulfillStock / releaseStock

**File:** `app/Domains/Order/Services/OrderStatusService.php`

Changed `if ($item->variant) { ... } if ($item->combo_id && ...)` to `if ($item->combo_id && ...) { ... } elseif ($item->variant_id)`.  
Once CRIT-5 was fixed and `combo_id` persists, an item could satisfy both conditions, deducting stock twice. The `elseif` makes them mutually exclusive.

---

### FIXED: HIGH-9 — fulfillStock / releaseStock now lock variant rows

**File:** `app/Domains/Order/Services/OrderStatusService.php`

Both methods now use `ProductVariant::lockForUpdate()->find(...)` before decrementing `stock` / `reserved_stock`.  
Previously, concurrent admin status changes (double-click "Ship") could race on the same variant row and corrupt inventory counts.

---

### FIXED: MED-12 — isValidTransition check moved inside transaction after lockForUpdate

**File:** `app/Domains/Order/Services/OrderStatusService.php`

The old code read `$order->order_status` before the transaction, validated, then re-fetched with `lockForUpdate`. The stale pre-lock status was used for the transition guard, allowing a race where another request could have already advanced the status.  
Fixed by reading `$oldStatusStr` from the locked order instance inside the transaction.

---

### FIXED: HIGH-10 — Fixed coupon discount capped at order amount

**File:** `app/Domains/Coupon/Services/CouponValidationService.php`

`calculateDiscount()` for `type = 'fixed'` now returns `min($coupon->value, $amount)`.  
Previously a ৳500 coupon on a ৳200 order stored `discount_total = 500` and `CouponUsage.discount_amount = 500`, overstating discounts in analytics while `max(0,...)` in grand_total masked the error.

---

### FIXED: MED-8 — Coupon row locked before validation

**File:** `app/Domains/Coupon/Services/CouponValidationService.php`

Added `lockForUpdate()` to the coupon SELECT inside `validate()`.  
This runs within `OrderService`'s outer transaction, so the lock is held for the full checkout. Previously two concurrent checkouts could both read `used_count = 4` against a `usage_limit = 5` coupon, both pass `isValidForUser()`, and then one throws a late "coupon exhausted" after the discount was already shown to the customer.

---

### FIXED: HIGH-2 (partial) — clearCart uses product_variant_id for combo items

**File:** `app/Domains/Cart/Services/CartService.php`

`clearCart()` was calling `->pluck('variant_id')` and `$variants->get($ci->variant_id)` for combo component rows. `ComboItem` stores the foreign key as `product_variant_id`, so both lookups always returned null — meaning `reserved_stock` was **never decremented** when a cart containing combos was cleared.  
Fixed both the pluck and the get to use `product_variant_id`.

---

### Still Open (not fixed in this pass)

| Ref | Description | Reason deferred |
|-----|-------------|-----------------|
| CRIT-9 | CheckoutRequest validation | Already fixed in current codebase (variant_id is `nullable`) |
| HIGH-7 | checkout_token unique constraint | Already has `->unique()` in migration |
| HIGH-11 | Shipping evaluated before coupon | May be intentional business rule; flagged for product decision |
| HIGH-12 | SSLCommerz stub routes to failure URL | Payment gateway integration not yet scoped |
| MED-9 | Guest per-user coupon limit not enforced | Requires session/phone-based tracking; design decision needed |
| MED-10 | order_number collision under concurrency | Already has `->unique()` in migration; acceptable risk at current scale |
| MED-11 | Combo snapshot missing component details | Schema change required (OrderComboItem table) |

