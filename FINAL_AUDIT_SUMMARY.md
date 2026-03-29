# Bionic API — Final Audit Summary & Merge Decision

**Date:** 2026-03-29
**Branch Under Review:** `rhjoyOfficial`
**Audits Completed:** Product, Cart, Order, Auth/Security, Database

---

## 1. Top Critical Issues (System-Breaking)

| # | Issue | Module | Impact |
|---|-------|--------|--------|
| C1 | Checkout idempotency check outside transaction — duplicate orders | Order | Financial loss, double stock reservation |
| C2 | No `lockForUpdate()` on variants during checkout — overselling | Order | Inventory corruption under concurrency |
| C3 | `addItem()` stock check ignores existing cart quantity | Cart | Unlimited stock reservation |
| C4 | `combo_id` column missing from `cart_items` table | Cart | Combo cart feature completely non-functional |
| C5 | Product update destroys all variants (delete + recreate) | Product | Breaks cart_items/order_items FK references, loses tier prices |
| C6 | `order_items.product_id` and `variant_id` have NO foreign key constraints | Database | Orphan records, no referential integrity on financial data |
| C7 | `stock`/`reserved_stock` are nullable integers — NULL breaks increment/decrement | Database | Stock operations silently fail, negative stock possible |
| C8 | Grand total can go negative (no floor on coupon discount) | Order + DB | Business owes customer money on checkout |
| C9 | Checkout endpoint has no authentication — anyone can place orders | Auth | Order spam, stock reservation abuse |
| C10 | Admin Blade routes have zero authentication middleware | Auth | Admin panel HTML/JS exposed to public |
| C11 | `ProductRelationService` writes `type` instead of `relation_type` | Product | Cross-sell/upsell feature completely broken |
| C12 | Route shadowing: `/products/search` unreachable behind `{slug}` wildcard | Product | Search feature non-functional |
| C13 | `ProductService` uses `image` key instead of `thumbnail` | Product | Thumbnail upload silently fails on every create/update |
| C14 | `Notification::send()` fires synchronously in event constructor | Order | HTTP request blocks/crashes on slow notification channels |
| C15 | Mass assignment allows arbitrary `order_status`/`payment_status` values | Order | Attacker can set order to "delivered"/"paid" via checkout |

**Total Critical: 15**

---

## 2. High Risk Issues

| # | Issue | Module |
|---|-------|--------|
| H1 | `OrderItem` missing `variant()` relationship — cancellation never releases stock | Order |
| H2 | Shipping address collected but never saved to `order_addresses` table | Order |
| H3 | Order number `Str::random(6)` — collision risk at scale | Order |
| H4 | No `lockForUpdate()` on order during status transition — race condition | Order |
| H5 | Admin eager-loads `shippingZone` but relationship is `zone()` — always null | Order |
| H6 | Exception `$e->getMessage()` leaked to API clients | Order |
| H7 | `remove()` endpoint passes `variant_id` but service expects `cart_item_id` | Cart |
| H8 | `CartMergeService` lacks transaction and stock validation | Cart |
| H9 | N+1 query on `tierPrices` in cart pricing calculation | Cart |
| H10 | Combo duplicate-check always fails (column doesn't exist) — double stock reservation | Cart |
| H11 | `clearCart()` doesn't release combo reserved stock — permanent stock leak | Cart |
| H12 | `UpdateProductRequest` forces variants required + SKU unique fails on own records | Product |
| H13 | `ProductRelationController::destroy()` has zero input validation | Product |
| H14 | `PricingService` can return negative line-item totals | Product |
| H15 | `ProductVariant::discountPercent` division by zero on free items | Product |
| H16 | Debug `/test-pricing/` route exposed publicly in production | Auth + Product |
| H17 | Inconsistent Spatie permission middleware — only on view routes | Auth |
| H18 | Sanctum tokens never expire, no scopes, no admin/customer distinction | Auth |
| H19 | `SecureHeaders` middleware exists but never registered globally | Auth |
| H20 | `cascadeOnDelete` on `category_id` — deleting category wipes all products | Database |
| H21 | `coupon_id` on orders uses `nullOnDelete` — destroys financial audit trail | Database |
| H22 | `coupon_usages` table exists but is never populated — per-user limits unenforceable | Database |
| H23 | `user_id` on orders uses `nullOnDelete` — user deletion orphans orders | Database |
| H24 | No discount/tier-price snapshot on `order_items` — financial disputes unresolvable | Database |

**Total High: 24**

---

## 3. Medium Risk Issues

| # | Issue | Module |
|---|-------|--------|
| M1 | `isLateToShip()` crashes on null `placed_at` | Order |
| M2 | `processing` status has no timestamp column | Order |
| M3 | `CheckoutRequest` missing field length limits | Order |
| M4 | `OrderResource` missing customer info, coupon, payment method | Order |
| M5 | Session token takeover — no format validation on guest cart tokens | Cart + Auth |
| M6 | Cart merge lets attacker steal any guest cart via session token | Auth |
| M7 | No cart expiration — reserved stock leaks forever from abandoned carts | Cart |
| M8 | `CartItemResource` accesses undefined `combo` relationship | Cart |
| M9 | `LIKE` search on `longText` description column — full table scan | Product |
| M10 | N+1 on `category` in product listings | Product |
| M11 | Missing composite index `product_variants(product_id, is_active)` | Database |
| M12 | Missing unique constraint `product_tier_prices(variant_id, min_quantity)` | Database |
| M13 | No unique constraint on `product_relations` — duplicate recommendations | Database |
| M14 | No unique constraint on `combo_items` — duplicate variant in combo | Database |
| M15 | `AuthService` returns mixed types (JsonResponse vs array) — login crashes | Auth |
| M16 | No CORS configuration for SPA transition | Auth |
| M17 | Phone format variation bypasses unique constraint on registration | Auth |
| M18 | Rate limiter bypassable via rotating proxies | Auth |
| M19 | Customer account Blade pages have no auth middleware | Auth |
| M20 | No password reset flow exists | Auth |
| M21 | ~15 empty placeholder files across all modules (dead code) | All |

**Total Medium: 21**

---

## 4. Cross-Module Risk Analysis

### Cart <-> Order Consistency
- **BROKEN:** Cart reserves stock via `reserved_stock` increment, but checkout does its OWN stock check without considering reserved_stock totals. A variant could have 10 stock, 10 reserved_stock (fully reserved by carts), yet checkout only checks `hasStock()` against raw `stock` — allowing overselling.
- **BROKEN:** Cart combo items track bundles, but checkout only processes individual `variant_id` items. No combo-to-order translation exists.
- **BROKEN:** Cart clears on checkout (presumably), but no cart status update to `converted` is performed in `OrderService`.

### Order <-> DB Financial Correctness
- **BROKEN:** `order_items` has no FK constraints — products/variants can be deleted leaving orphaned financial records.
- **BROKEN:** No coupon code/discount snapshot — if coupon is deleted (`nullOnDelete`), order loses all coupon trace.
- **BROKEN:** `discount_total` merges tier discounts + coupon discount into one field — cannot separate for reporting.
- **BROKEN:** Grand total has no DB-level CHECK constraint — negative values can be stored.

### Auth <-> API Exposure
- **BROKEN:** Checkout is fully public (no auth). Admin Blade pages are fully public. Debug pricing route is public.
- **BROKEN:** Spatie permissions only enforced on 3 of ~20 admin routes.
- **BROKEN:** Sanctum tokens are immortal — leaked token = permanent access.
- **BROKEN:** `SecureHeaders` middleware written but never activated.

### Inventory <-> Cart Reservation
- **BROKEN:** `stock` and `reserved_stock` are nullable — `NULL + increment = NULL`, silently corrupting inventory.
- **BROKEN:** No cart expiration job — abandoned carts leak reserved stock permanently.
- **BROKEN:** Combo cart clearing doesn't release constituent variant stock.
- **BROKEN:** Product update deletes all variants, but `reserved_stock` on those variants is lost — phantom reservations become permanent.

---

## 5. Impact of Recent Migration Changes

**Last migration change:** `ed4ab57` (Auth service refactor) — no schema changes.

**Assessment:** No migrations have been modified since the audits were conducted. All DB audit findings remain fully valid. No previous issues were fixed. No new risks appeared from migration changes.

---

## 6. System Strengths

1. **Clean DDD structure** — Domains are well-separated (Auth, Cart, Order, Product, Coupon, Shipping). Easy to navigate and reason about.
2. **Sanctum + Spatie Permission foundation** — The auth infrastructure is solid; it just needs consistent enforcement.
3. **Reserved stock pattern** — The concept of separating `stock` vs `reserved_stock` is architecturally sound. Implementation needs fixes but the design is correct.
4. **Tier pricing engine** — `PricingService` correctly calculates quantity-based discounts with proper tier matching logic.
5. **Standardized API responses** — `ApiResponse` helper provides consistent JSON structure across most endpoints.
6. **Checkout token idempotency** — The concept exists and the `checkout_token` column has a unique constraint. Just needs to move inside the transaction.
7. **Order status state machine** — `OrderStatusService` with `isValidTransition()` is well-designed. Valid transition map is correct.
8. **Good migration discipline** — All money columns use `decimal(10,2)`, not float. Proper enum types for statuses. Timestamps on all tables.
9. **Rate limiting on auth** — Login endpoint has proper throttling with per-IP tracking.
10. **Eager loading awareness** — Most controllers load relationships; the gaps are identifiable and fixable.

---

## 7. Production Readiness Score

| Category | Score | Notes |
|----------|:-----:|-------|
| Data Integrity | 2/10 | Missing FKs, nullable stock, no snapshots, cascade deletes on financial data |
| Financial Correctness | 3/10 | Negative totals possible, coupon tracking incomplete, no audit trail |
| Inventory Safety | 2/10 | Overselling possible, stock leaks from abandoned carts, combo stock broken |
| Authentication & Security | 3/10 | Unauthenticated checkout, public admin pages, no token expiry |
| API Correctness | 4/10 | 3 features completely broken (search, relations, thumbnails), inconsistent responses |
| Concurrency Safety | 2/10 | No locking on checkout, status changes, or cart operations |
| Performance | 5/10 | Multiple N+1 queries, LIKE on longText, missing indexes |
| Code Quality | 6/10 | Clean structure, good DDD separation, but ~15 empty files and dead code |
| SPA Readiness | 4/10 | No CORS config, incomplete API resources, missing data in responses |

**Overall Production Readiness: 3.0 / 10**

---

## 8. FINAL DECISION

### NEEDS FIXES BEFORE MERGE

The system has **15 Critical** and **24 High** severity issues. Multiple core features are completely non-functional (search, product relations, combo cart, thumbnail uploads). Financial integrity is compromised (negative totals, no audit trail, orphaned records). Authentication has fundamental gaps (public checkout, public admin pages, immortal tokens). Inventory management will corrupt under any concurrent load.

**This codebase is not safe for production traffic in its current state.**

---

## 9. Required Fix Checklist

### PHASE 1 — Must Fix Before Any Merge (Critical Blockers)

**Inventory & Checkout (highest priority):**
- [ ] Move idempotency check inside DB transaction with `lockForUpdate()` — `OrderService.php`
- [ ] Add `lockForUpdate()` to `loadVariantsForItems()` — `OrderService.php`
- [ ] Fix `addItem()` stock check to validate total qty (existing + new) — `CartService.php`
- [ ] Change `stock`/`reserved_stock` to `unsignedInteger()->default(0)` — migration
- [ ] Add `max(0, ...)` floor to grand total calculation — `OrderService.php`
- [ ] Remove `order_status`/`payment_status`/`payment_method` from `Order::$fillable`

**Broken Features (non-functional without fix):**
- [ ] Add `combo_id` column to `cart_items` migration + `CartItem::$fillable`
- [ ] Move `/products/search` route ABOVE `/products/{slug}` wildcard
- [ ] Fix `ProductRelationService` to use `relation_type` not `type`
- [ ] Fix `ProductService` to use `thumbnail` not `image`
- [ ] Replace variant delete+recreate with upsert logic in `ProductService::update()`

**Security (exposure to attacks):**
- [ ] Add auth middleware to admin Blade routes in `web.php`
- [ ] Add auth middleware to customer account routes in `web.php`
- [ ] Remove or gate `/test-pricing/` debug route
- [ ] Move `Notification::send()` out of `OrderStatusChanged` constructor into queued listener

### PHASE 2 — Must Fix Before Production Traffic

**Data Integrity:**
- [ ] Add FK constraints on `order_items.product_id` and `order_items.variant_id` (`restrictOnDelete`)
- [ ] Change `cascadeOnDelete` to `restrictOnDelete` on `products.category_id`
- [ ] Change `nullOnDelete` to `restrictOnDelete` on `orders.coupon_id`
- [ ] Populate `coupon_usages` table in `OrderService`
- [ ] Add `variant()` relationship to `OrderItem` model
- [ ] Save shipping address to `order_addresses` table in `OrderService`
- [ ] Add discount snapshot columns to `order_items`

**Auth Hardening:**
- [ ] Add token expiry to Sanctum `createToken()` calls
- [ ] Register `SecureHeaders` middleware globally in `bootstrap/app.php`
- [ ] Apply consistent Spatie permission middleware on ALL admin routes
- [ ] Fix `AuthService` to return arrays consistently (not mixed JsonResponse/array)

**Cart Fixes:**
- [ ] Fix `remove()` to pass `cart_item_id` not `variant_id`
- [ ] Fix `clearCart()` to handle combo multi-variant stock release
- [ ] Wrap `CartMergeService::merge()` in `DB::transaction` with stock validation
- [ ] Add `lockForUpdate()` on order during status change in `OrderStatusService`

### PHASE 3 — Should Fix Before Scaling

- [ ] Add cart expiration scheduled job to release stale reserved stock
- [ ] Add composite indexes: `product_variants(product_id, is_active)`, `cart_items(cart_id, variant_id)`
- [ ] Add unique constraints: `product_tier_prices(variant_id, min_quantity)`, `product_relations(product_id, related_product_id, relation_type)`
- [ ] Add `processing_at` timestamp to orders migration
- [ ] Configure CORS for SPA transition
- [ ] Normalize phone numbers before unique validation
- [ ] Add per-account rate limiting (not just per-IP)
- [ ] Eager-load `category` in product listings, `tierPrices` in cart pricing
- [ ] Replace `LIKE` on `longText` with `short_description` search
- [ ] Increase order number random length from 6 to 10+ characters
- [ ] Remove ~15 empty placeholder files across all modules
- [ ] Implement password reset flow

---

*End of Final Audit Summary*
