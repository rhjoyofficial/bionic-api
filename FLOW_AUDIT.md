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
