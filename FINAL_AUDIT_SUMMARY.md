# FINAL AUDIT SUMMARY

## 1) Critical Issues (system-breaking)

1. **Cart combo insert is structurally broken** (Cart + DB): `CartService::addCombo()` creates cart items without `variant_id`, but `cart_items.variant_id` is non-null and unique with `(cart_id, variant_id)`. Combo add can fail at runtime or violate schema intent for multi-item bundles.  
   - Impact: bundle checkout/cart flow can hard-fail in production.

2. **Order placement does not consume cart reservations** (Cart ↔ Order ↔ Inventory): checkout reserves stock again at order creation, but there is no cart lock/convert/release integration in checkout path.  
   - Impact: inflated `reserved_stock`, false stock-outs, inconsistent availability under load.

3. **Order item write mismatches migration contract** (Order ↔ DB): `order_items` migration requires `original_unit_price`, `variant_title_snapshot` (non-null), and supports additional snapshot fields, while `OrderService::create()` only writes a subset.  
   - Impact: order creation can fail on NOT NULL constraints or produce partial financial snapshot integrity.

## 2) High Risk Issues

1. **Cart merge references non-existent stock field** (Auth ↔ Cart): `CartMergeService` checks `$item->variant->stock_quantity`, but variants expose `stock` / `reserved_stock` / `available_stock`.  
   - Impact: login/register cart merge can crash or skip valid stock checks.

2. **No permission checks on multiple admin endpoints** (Auth + API security): tier prices, product relations, shipping zone/coupon/webhook resource routes are not uniformly guarded with explicit permission middleware unlike products/categories/orders.
   - Impact: role-authenticated admins may access operations beyond intended fine-grained permissions.

3. **Potential duplicate coupon usage rows under retry races** (Order financial integrity): coupon decrement and usage insert occur without an idempotency/uniqueness guard on `coupon_usages` (e.g., unique `order_id` or `(coupon_id,user_id,order_id)`).
   - Impact: overcounted coupon consumption and reporting mismatch under repeated requests.

4. **Stock decrement path on fulfillment is incomplete** (Order lifecycle): cancellation releases `reserved_stock`, but there is no clear shipped/delivered transition logic that deducts physical `stock` and clears reservation atomically.
   - Impact: long-term inventory drift between reserved and real stock.

## 3) Medium Risk Issues

1. **Admin route prefix duplication**: admin routes are declared with `prefix('admin')` twice, creating `/admin/admin/...` API paths unexpectedly.
2. **Cart totals can drift from stored snapshots**: `CartPricingService` recalculates variant pricing live instead of relying strictly on snapshot values for all line items.
3. **Missing DB indexes for heavy financial/event tables**: `order_transactions` lacks explicit index on `order_id`/`type`; `commissions` lacks status/referrer indexes likely needed for payout/ledger queries.
4. **Secure headers are partial**: no CSP/Permissions-Policy and legacy X-XSS header retained; hardening exists but not full modern API/web posture.

## 4) Cross-module risks

### Cart ↔ Order
- Checkout is not clearly coupled to cart ownership/session token, so reservation accounting can be duplicated instead of transferred.
- No explicit “cart converted” state transition when an order succeeds.

### Order ↔ DB
- Order-item snapshot schema vs runtime writes are misaligned, risking failed writes and unreliable audit trails.
- Financial transactions table exists but is not integrated into checkout flow for immutable ledgering.

### Auth ↔ API
- Sanctum + role middleware exists, but route-level permission granularity is inconsistent across admin modules.
- Cart merge is triggered during auth but can fail due to invalid stock field usage.

### Inventory ↔ Cart
- Reservation model (`stock - reserved_stock`) is conceptually solid, but combo/cart/order flows do not consistently lock/transfer/release reservation ownership.
- Reservation cleanup depends on scheduled abandonment, not guaranteed order/cart lifecycle hooks.

## 5) System Strengths

- Uses DB transactions and row-level locking (`lockForUpdate`) in critical cart/order mutations.
- Variant-level inventory abstraction (`available_stock`) is explicit and reusable.
- Checkout token idempotency mechanism exists for duplicate order-submit protection.
- Core entities have foreign keys and major product/order tables include meaningful indexes.

## 6) Production Readiness Score (0–10)

**4.5 / 10**

## 7) FINAL DECISION

**NEEDS FIXES BEFORE MERGE**

## 8) Required Fix Checklist

- [ ] Make `cart_items` schema/service consistent for combo lines (nullable `variant_id` or normalized combo-line design with proper unique/index strategy).
- [ ] Implement cart→order conversion workflow: lock cart, transfer/reconcile reservations, mark cart converted, prevent double-reserve.
- [ ] Align `OrderService` order-item inserts with migration-required snapshot columns.
- [ ] Fix `CartMergeService` stock field usage to `available_stock` logic and add lock-aware stock validation.
- [ ] Add missing permission middleware to all sensitive admin endpoints.
- [ ] Add uniqueness/idempotency constraints for coupon usage records tied to an order.
- [ ] Define inventory finalization policy (when `stock` decrements) and implement atomically in status transitions.
- [ ] Add missing operational indexes for transaction/commission query paths.
