# Bionic API - Cart Module Technical Audit

**Date:** 2026-03-26
**Branch:** `rhjoyOfficial`
**Scope:** `app/Domains/Cart/` (7 files), related migrations

---

## Critical Risks

### CART-01: `addItem()` stock check ignores already-reserved quantity in cart

**File:** `CartService.php:60-70`

When a user adds an item that already exists in their cart, the code checks `$variant->hasStock($qty)` — which only checks if the *new increment* fits. It never validates whether the *total* quantity (`existing + new`) is available. The variable `$newQty` on line 68 is computed but never used for the stock check.

**Before (broken):**
```php
$item = $cart->items()->where('variant_id', $variantId)->first();

if ($item) {
    $newQty = $item->quantity + $qty;       // computed but NEVER USED

    if (! $variant->hasStock($qty)) {       // checks only the increment, not total
        throw new Exception("Stock limit reached");
    }

    $item->increment('quantity', $qty);
    $variant->increment('reserved_stock', $qty);
    return $item;
}
```

**After (fixed):**
```php
if ($item) {
    $newQty = $item->quantity + $qty;

    if (! $variant->hasStock($newQty)) {    // check TOTAL quantity against stock
        throw new Exception("Stock limit reached");
    }

    $item->increment('quantity', $qty);
    $variant->increment('reserved_stock', $qty);
    return $item;
}
```

**Impact:** A user can reserve unlimited stock by repeatedly adding qty=1 to an item that already has stock at capacity. This corrupts inventory availability across the entire system.

---

### CART-02: `addCombo()` creates cart item with `combo_id` but `cart_items` table has no `combo_id` column

**File:** `CartService.php:33,38-43` vs `cart_items` migration

The `cart_items` migration defines: `cart_id`, `variant_id`, `quantity`, `unit_price_snapshot`, `product_name_snapshot`, `variant_title_snapshot`, `combo_name_snapshot`. There is **no `combo_id` column**.

Yet `addCombo()` does:
```php
$cartItem = $cart->items()->where('combo_id', $comboId)->first();  // line 33
// and
$cartItem = $cart->items()->create([
    'combo_id' => $comboId,      // line 39 — column does not exist
    ...
]);
```

And `CartItemResource` references `$this->combo_id` (line 20) and `$this->combo` (line 21).

The `CartItem` model `$fillable` also does NOT include `combo_id`.

**Impact:** Every `addCombo()` call will either silently drop the `combo_id` (because it's not in `$fillable`) or throw a SQL error. The combo cart feature is completely non-functional.

**Fix:** Add a migration:
```php
$table->foreignId('combo_id')->nullable()->constrained()->nullOnDelete();
```
And add `'combo_id'` to `CartItem::$fillable`.

---

### CART-03: `getCart()` can create carts for anyone — session token takeover

**File:** `CartService.php:13-20`, `CartController.php:152-158`

`getCart()` uses `firstOrCreate` with the session token from the request header. There is zero validation on the session token format or ownership. Any client can:

1. Guess or enumerate session tokens (they're just strings)
2. Pass `X-Session-Token: victim-token` to read/modify another guest's cart
3. For authenticated users: `Auth::id()` is used, but a logged-in user can still pass a `session_token` — the service ignores it when `userId` is set, which is correct. However, `getCart()` with `userId=null` and a guessable session token is exploitable.

**Before:**
```php
private function resolveCart(Request $request)
{
    return $this->cartService->getCart(
        Auth::id(),
        $request->header('X-Session-Token') ?? $request->session_token
    );
}
```

**Fix:** Require session tokens to be cryptographically random UUIDs, and validate format:
```php
private function resolveCart(Request $request)
{
    $token = $request->header('X-Session-Token') ?? $request->session_token;

    if (!Auth::id() && !$token) {
        throw new Exception('Session token required for guest cart');
    }

    if ($token && !Str::isUuid($token)) {
        throw new Exception('Invalid session token format');
    }

    return $this->cartService->getCart(Auth::id(), $token);
}
```

---

### CART-04: `clearCart()` only releases stock for regular items, not combo items

**File:** `CartService.php:131-144`

`clearCart()` iterates over items and decrements `reserved_stock` using `$item->variant_id`. But combo cart items may have a null `variant_id` (they use `combo_id` to track constituent variants). The code finds a single variant per item, but a combo item reserves stock across *multiple* variants via `combo->items`.

**Before:**
```php
public function clearCart(Cart $cart)
{
    return DB::transaction(function () use ($cart) {
        foreach ($cart->items as $item) {
            $variant = ProductVariant::lockForUpdate()->find($item->variant_id);
            $variant?->decrement('reserved_stock', $item->quantity);
        }
        $cart->items()->delete();
    });
}
```

**After (handles combos):**
```php
public function clearCart(Cart $cart)
{
    return DB::transaction(function () use ($cart) {
        foreach ($cart->items as $item) {
            if ($item->combo_id) {
                $combo = Combo::with('items.variant')->find($item->combo_id);
                if ($combo) {
                    foreach ($combo->items as $ci) {
                        $ci->variant->decrement('reserved_stock', $ci->quantity * $item->quantity);
                    }
                }
            } else {
                $variant = ProductVariant::lockForUpdate()->find($item->variant_id);
                $variant?->decrement('reserved_stock', $item->quantity);
            }
        }
        $cart->items()->delete();
    });
}
```

**Impact:** Clearing a cart with combo items leaks reserved stock permanently. Those variant units become permanently "reserved" and unavailable for purchase.

---

## High Risks

### CART-05: `remove()` endpoint passes `variant_id` but `removeItem()` expects a cart item ID

**File:** `CartController.php:121-123` vs `CartService.php:114`

The controller passes `$request->variant_id`:
```php
$this->cartService->removeItem($cart, $request->variant_id);
```

But `removeItem()` does:
```php
public function removeItem(Cart $cart, int $itemId)
{
    $item = $cart->items()->findOrFail($itemId);  // finds by primary key
```

`findOrFail($itemId)` searches by the cart_item's primary key `id`, not by `variant_id`. If `variant_id = 5` but the cart_item `id = 12`, it will either find the wrong item or 404.

Additionally, the `remove` endpoint has **no input validation at all** — no `$request->validate()`.

**Fix:**
```php
// CartController.php - remove()
public function remove(Request $request)
{
    $request->validate([
        'cart_item_id' => 'required|exists:cart_items,id',
    ]);

    $cart = $this->resolveCart($request);
    $this->cartService->removeItem($cart, $request->cart_item_id);
    // ...
}
```

---

### CART-06: `CartMergeService` does not validate stock or update reserved_stock

**File:** `CartMergeService.php:29-48`

When merging a guest cart into a user cart, if the same variant exists in both carts, the quantities are summed:
```php
$existing->increment('quantity', $item->quantity);
```

But there is:
1. **No stock check** — the merged total may exceed available stock
2. **No reserved_stock accounting** — the reserved_stock was already incremented when the guest added items, but after merge the guest cart is deleted. The reservation attribution is now wrong (guest cart gone, user cart has items but reserved_stock was tied to the guest cart's additions). This is *technically* fine only if we assume reserved_stock is global per variant, but the merge does not re-validate availability.
3. **No transaction** — if the merge fails midway, some items are copied and some aren't, and the guest cart is deleted on line 51 regardless.

**Fix:** Wrap in `DB::transaction()` and add stock validation:
```php
public function merge(string $sessionToken, int $userId): void
{
    DB::transaction(function () use ($sessionToken, $userId) {
        // ... existing logic ...
        // Add: validate stock before increment
        // Add: don't delete guest cart until merge completes (already inside txn)
    });
}
```

---

### CART-07: `CartPricingService` triggers N+1 query on `tierPrices` for every non-combo item

**File:** `CartPricingService.php:22-25`

For each cart item, `PricingService->calculate($item->variant, ...)` is called. Inside `PricingService`, if no tiers are passed, it queries `$variant->tierPrices` — one query per item.

The `payload()` method in `CartController` eager-loads `items.variant.product` but NOT `items.variant.tierPrices`.

**Before:**
```php
// CartController.php:162
$cart->load('items.variant.product');
```

**After:**
```php
$cart->load('items.variant.product', 'items.variant.tierPrices');
```

---

### CART-08: `addCombo()` duplicate-check bug — `combo_id` not in `$fillable`, so existing combos are never found

**File:** `CartService.php:33`

```php
$cartItem = $cart->items()->where('combo_id', $comboId)->first();
```

Since `combo_id` doesn't exist on the DB table (see CART-02), this query always returns null, so the same combo is added as a new cart item every time. This means double stock reservation on every repeat add.

**Impact:** Stock is reserved multiplicatively on every call. 5 clicks = 5 separate rows, each reserving stock independently.

---

## Medium Risks

### CART-09: `getCart()` with `firstOrCreate` has a race condition for guest carts

**File:** `CartService.php:13-20`

```php
return Cart::firstOrCreate([
    'user_id' => $userId,
    'session_token' => $userId ? null : $sessionToken,
    'status' => 'active'
]);
```

Two concurrent requests with the same session token can both pass the `first()` check simultaneously and both attempt `create()`. Because `session_token` has a unique constraint in the DB, the second request will throw a unique constraint violation exception — resulting in a 500 error to the user.

**Fix:** Use try/catch or `firstOrCreate` inside a `DB::transaction` with proper exception handling:
```php
public function getCart(?int $userId, ?string $sessionToken): Cart
{
    try {
        return Cart::firstOrCreate([
            'user_id' => $userId,
            'session_token' => $userId ? null : $sessionToken,
            'status' => 'active'
        ]);
    } catch (\Illuminate\Database\QueryException $e) {
        return Cart::where('session_token', $sessionToken)
            ->where('status', 'active')
            ->firstOrFail();
    }
}
```

---

### CART-10: `CartItemResource` accesses `combo` and `combo->image` without eager loading — N+1 + crash

**File:** `CartItemResource.php:20-22`

```php
'image_url' => $this->combo_id
    ? ($this->combo->image ?? null)       // triggers lazy load per item
    : ($this->variant->product->image_url ?? null),
```

`$this->combo` is never eager-loaded anywhere. Every combo cart item triggers a separate query. Also, `CartItem` model has no `combo()` relationship defined, so `$this->combo` will return `null` via magic __get and silently fail, or crash depending on Laravel version.

**Fix:** Add `combo` relationship to `CartItem` model and eager-load it in the controller payload.

---

### CART-11: No cart expiration mechanism — reserved stock leaks forever

**File:** Architectural gap (no file)

Carts remain `active` indefinitely. There is no scheduled job or TTL to:
- Mark carts as `abandoned` after N hours
- Release `reserved_stock` for abandoned carts

Over time, reserved stock accumulates from abandoned sessions, reducing sellable inventory permanently.

**Fix:** Add a scheduled command:
```php
// Run hourly
Cart::where('status', 'active')
    ->where('updated_at', '<', now()->subHours(24))
    ->each(function ($cart) {
        app(CartService::class)->clearCart($cart);
        $cart->update(['status' => 'abandoned']);
    });
```

---

### CART-12: Missing `combo_id` relationship on `CartItem` model

**File:** `CartItem.php`

The model defines `cart()` and `variant()` relationships, but no `combo()` relationship. Yet `CartItemResource` accesses `$this->combo` and `$this->combo_id`. Missing relationship declaration.

**Fix:**
```php
public function combo(): BelongsTo
{
    return $this->belongsTo(\App\Models\Combo::class);
}
```

---

### CART-13: Missing composite index on `cart_items(cart_id, variant_id)`

**File:** `cart_items` migration

Every `addItem()` call does: `$cart->items()->where('variant_id', $variantId)->first()`. Without a composite index, this requires a scan of all items in the cart.

**Fix:**
```php
$table->index(['cart_id', 'variant_id']);
```

---

### CART-14: `update` endpoint validates `cart_item_id` exists globally, not scoped to user's cart

**File:** `CartController.php:93-94`

```php
$request->validate([
    'cart_item_id' => 'required|exists:cart_items,id',
]);
```

This only checks that the cart_item exists in the table — not that it belongs to the current user's cart. A user could update quantities on another user's cart item by guessing IDs. The service does scope via `$cart->items()->findOrFail($cartItemId)`, which provides a second check, but the validation rule is misleading and the error message would be confusing.

**Fix:** Remove the `exists` rule (the service handles it) or add a custom rule scoping to the cart.

---

## Issue Summary Table

| #  | Issue | Severity | Category |
|----|-------|----------|----------|
| 01 | `addItem()` stock check ignores existing cart quantity | **Critical** | Stock Corruption |
| 02 | `combo_id` column missing from `cart_items` table | **Critical** | Schema Bug |
| 03 | Session token takeover — no validation on guest tokens | **Critical** | Security |
| 04 | `clearCart()` doesn't release combo reserved stock | **Critical** | Stock Leak |
| 05 | `remove()` passes `variant_id` but service expects item ID | **High** | Logic Bug |
| 06 | Cart merge skips stock validation and has no transaction | **High** | Data Integrity |
| 07 | N+1 on `tierPrices` in `CartPricingService` | **High** | Performance |
| 08 | Combo duplicate-check always fails due to missing column | **High** | Stock Corruption |
| 09 | Race condition on `firstOrCreate` for guest carts | **Medium** | Concurrency |
| 10 | `CartItemResource` accesses undefined `combo` relationship | **Medium** | Runtime Error |
| 11 | No cart expiration — reserved stock leaks forever | **Medium** | Architecture |
| 12 | Missing `combo()` relationship on `CartItem` model | **Medium** | Missing Code |
| 13 | Missing composite index on `cart_items(cart_id, variant_id)` | **Medium** | Performance |
| 14 | `cart_item_id` exists validation not scoped to user's cart | **Medium** | Security |

---

## Suggested Fixes Checklist

### Must fix before merge
- [ ] Fix `addItem()` stock check to validate total qty (`existing + new`) — CART-01
- [ ] Add `combo_id` column to `cart_items` migration + add to `CartItem::$fillable` — CART-02
- [ ] Add session token format validation (UUID) + require for guests — CART-03
- [ ] Fix `clearCart()` to handle combo items' multi-variant stock release — CART-04
- [ ] Fix `remove()` to pass `cart_item_id` instead of `variant_id`, add validation — CART-05

### Should fix before production
- [ ] Wrap `CartMergeService::merge()` in `DB::transaction`, add stock checks — CART-06
- [ ] Eager-load `items.variant.tierPrices` in `CartController::payload()` — CART-07
- [ ] Add `combo()` BelongsTo relationship to `CartItem` model — CART-12
- [ ] Add composite index `cart_items(cart_id, variant_id)` — CART-13

### Should fix before scale
- [ ] Add scheduled cart abandonment job to release stale reserved stock — CART-11
- [ ] Handle `firstOrCreate` race condition on concurrent guest requests — CART-09
- [ ] Eager-load `combo` in cart payload to avoid N+1 in resource — CART-10
- [ ] Scope `cart_item_id` validation to current user's cart — CART-14

---

*End of Cart Module Audit*
