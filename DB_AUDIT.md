# Database & Financial Integrity Audit Report

**Scope:** `database/migrations/**` (25 migration files, 20 tables)
**Date:** 2026-03-28
**Branch:** `rhjoyOfficial`

---

## Critical Risks

### 1. order_items: Missing Foreign Key Constraints on product_id and variant_id

**File:** `2026_02_27_153938_create_order_items_table.php:21-22`
**Impact:** `product_id` and `variant_id` are declared via `foreignId()` but have NO `->constrained()` call. There are no actual foreign key constraints in the database. This means:
- Order items can reference non-existent products/variants
- Deleting a product or variant leaves orphaned order items with dangling IDs
- No referential integrity whatsoever on the most critical financial table

```php
// BEFORE
$table->foreignId('product_id');
$table->foreignId('variant_id');
```

```php
// AFTER — add constraints (soft approach for orders)
$table->foreignId('product_id')
    ->constrained()
    ->restrictOnDelete(); // prevent product deletion if orders exist

$table->foreignId('variant_id')
    ->constrained('product_variants')
    ->restrictOnDelete(); // prevent variant deletion if orders exist
```

**Note:** Use `restrictOnDelete()` not `cascadeOnDelete()` — deleting a product must NOT silently delete order history.

---

### 2. order_items: Missing Indexes on product_id and variant_id

**File:** `2026_02_27_153938_create_order_items_table.php`
**Impact:** Without foreign key constraints, MySQL does not auto-create indexes on `product_id` and `variant_id`. Any query joining order_items to products or variants (admin reports, "customers also bought", reorder flows) will do full table scans. This becomes a serious bottleneck as orders grow.

```php
// AFTER — add indexes
$table->index('product_id');
$table->index('variant_id');
$table->index('order_id'); // already has FK, but verify
```

---

### 3. stock and reserved_stock Allow NULL — Negative Stock Possible

**File:** `2026_02_27_153731_create_product_variants_table.php:27-28`
**Impact:** Both `stock` and `reserved_stock` are `integer()->nullable()`. This means:
- `NULL` stock is ambiguous — does it mean "unlimited" or "not tracked"?
- Application code using `$variant->hasStock($qty)` must handle NULL, and likely doesn't
- `decrement('reserved_stock', $qty)` on NULL produces NULL, not a negative number — stock release silently fails
- No `unsigned()` constraint means negative stock is possible at the DB level

```php
// BEFORE
$table->integer('stock')->nullable();
$table->integer('reserved_stock')->nullable();
```

```php
// AFTER
$table->unsignedInteger('stock')->default(0);
$table->unsignedInteger('reserved_stock')->default(0);
// Add CHECK constraint:
// DB::statement('ALTER TABLE product_variants ADD CONSTRAINT chk_stock CHECK (reserved_stock <= stock)');
```

---

### 4. Grand Total Can Go Negative — No DB-Level Guard

**File:** `2026_02_27_153902_create_orders_table.php:33`
**Impact:** `grand_total` is `decimal(10,2)` with no unsigned constraint or check constraint. Application code calculates `subtotal - discountTotal - couponDiscount + shipping` which can produce negative values if coupon exceeds subtotal. Negative orders mean the business "owes" the customer money.

```php
// AFTER — add CHECK constraint via raw SQL in migration
DB::statement('ALTER TABLE orders ADD CONSTRAINT chk_grand_total CHECK (grand_total >= 0)');
DB::statement('ALTER TABLE orders ADD CONSTRAINT chk_subtotal CHECK (subtotal >= 0)');
```

---

### 5. coupon_usages Table Exists But Is Never Populated

**File:** `2026_02_27_153904_create_coupon_usages_table.php` vs `OrderService.php`
**Impact:** The `coupon_usages` table has a proper schema with `coupon_id`, `user_id`, `order_id`, and `discount_amount`. However, `OrderService::create()` only calls `Coupon::increment('used_count')` — it never creates a `coupon_usages` record. This means:
- Per-user coupon limits (`limit_per_user`) are unenforceable
- No audit trail of which user used which coupon on which order
- The `discount_amount` per usage is never recorded
- `used_count` on coupons table is the only tracking — and it can drift if orders are cancelled

```php
// AFTER — in OrderService, after coupon increment:
CouponUsage::create([
    'coupon_id' => $couponId,
    'user_id' => Auth::id(),
    'order_id' => $order->id,
    'discount_amount' => $couponDiscount,
]);
```

---

### 6. Orders: coupon_id Uses nullOnDelete — Financial Record Destroyed

**File:** `2026_02_27_153902_create_orders_table.php:35-38`
**Impact:** `coupon_id` has `nullOnDelete()`. If an admin deletes a coupon, all orders that used it lose the reference. Financial reports can no longer trace which coupon was applied. This destroys auditability.

```php
// BEFORE
$table->foreignId('coupon_id')
    ->nullable()
    ->constrained()
    ->nullOnDelete();
```

```php
// AFTER — restrict deletion or use soft deletes on coupons
$table->foreignId('coupon_id')
    ->nullable()
    ->constrained()
    ->restrictOnDelete();
// Or add SoftDeletes to the Coupon model
```

---

## High Risks

### 7. Products: cascadeOnDelete on category_id — Deleting Category Deletes All Products

**File:** `2026_02_27_153707_create_products_table.php:21-23`
**Impact:** Deleting a category cascades to delete all products in that category, which cascades to delete all variants, which cascades to delete all tier prices. A single accidental category deletion wipes out products, inventory data, and pricing. No soft-delete safety net.

```php
// BEFORE
$table->foreignId('category_id')
    ->constrained()
    ->cascadeOnDelete();
```

```php
// AFTER
$table->foreignId('category_id')
    ->constrained()
    ->restrictOnDelete();
// Admin must reassign products before deleting a category
```

---

### 8. order_items: No Discount/Tier-Price Snapshot

**File:** `2026_02_27_153938_create_order_items_table.php`
**Impact:** `order_items` stores `unit_price` and `total_price` but does not snapshot:
- `discount_type` / `discount_value` applied to the variant at time of purchase
- Tier price discount amount
- Original price before discount
This makes it impossible to audit how `total_price` was calculated. Financial disputes cannot be resolved from DB data alone.

```php
// AFTER — add snapshot columns
$table->decimal('original_unit_price', 10, 2); // price before any discount
$table->string('discount_type_snapshot')->nullable(); // 'percentage', 'fixed', 'tier'
$table->decimal('discount_value_snapshot', 10, 2)->nullable();
```

---

### 9. Orders: No coupon_code or coupon_discount Snapshot

**File:** `2026_02_27_153902_create_orders_table.php`
**Impact:** The orders table stores `coupon_id` and `discount_total` (which combines tier discounts + coupon discount). There is no separate `coupon_discount` column or `coupon_code` snapshot. If coupon is deleted (nullOnDelete), there is zero trace of coupon usage. And `discount_total` merges two unrelated discount types into one number.

```php
// AFTER — add to orders table
$table->string('coupon_code_snapshot')->nullable();
$table->decimal('coupon_discount', 10, 2)->default(0);
// And change discount_total to only reflect item/tier discounts
```

---

### 10. cart_items: No Foreign Key Index on variant_id

**File:** `2026_03_04_053331_create_cart_items_table.php:17`
**Impact:** `variant_id` has `->constrained()` which creates a FK (and MySQL auto-creates an index for FKs). However, there's no composite index for common queries like "find cart item by cart + variant". Cart operations that check for existing items do `WHERE cart_id = ? AND variant_id = ?` without a covering index.

```php
// AFTER
$table->unique(['cart_id', 'variant_id']); // also prevents duplicate items
```

---

### 11. carts: Both user_id and session_token Nullable — Ghost Carts Possible

**File:** `2026_03_04_053308_create_carts_table.php:16-17`
**Impact:** Both `user_id` and `session_token` are nullable. A cart with both NULL has no owner — it's an orphan that can never be retrieved. The `firstOrCreate` in CartService with `user_id=null, session_token=null` would match the first orphan cart.

```php
// AFTER — add CHECK constraint
DB::statement('ALTER TABLE carts ADD CONSTRAINT chk_cart_owner CHECK (user_id IS NOT NULL OR session_token IS NOT NULL)');
```

---

### 12. Orders: user_id Uses nullOnDelete — Breaks User Order History

**File:** `2026_02_27_153902_create_orders_table.php:18-21`
**Impact:** `nullOnDelete()` means deleting a user sets `user_id` to NULL on all their orders. Those orders become "anonymous" and cannot be linked to any account for customer service, returns, or analytics. Guest orders are already `user_id = NULL`, so you can't distinguish "guest order" from "deleted user order".

```php
// AFTER
$table->foreignId('user_id')
    ->nullable()
    ->constrained()
    ->restrictOnDelete();
// Use soft deletes on users instead
```

---

## Medium Risks

### 13. product_variants: No Index on product_id

**File:** `2026_02_27_153731_create_product_variants_table.php:17-19`
**Impact:** FK constraint creates an auto-index in MySQL, but this should be explicitly declared. Every product page query loads variants by `product_id`. No composite index on `(product_id, is_active)` for filtered queries.

```php
// AFTER
$table->index(['product_id', 'is_active']);
```

---

### 14. coupons: No Index on code Column Lookups

**File:** `2026_02_27_153842_create_coupons_table.php:16`
**Impact:** `code` has `->unique()` which creates a unique index — this is correct. However, there's no index on `(is_active, start_date, end_date)` for the common query "find active coupons within valid date range".

```php
// AFTER
$table->index(['is_active', 'start_date', 'end_date']);
```

---

### 15. courier_shipments: No Index on tracking_code or status

**File:** `2026_03_07_153203_create_courier_shipments_table.php`
**Impact:** `tracking_code` has no index. Customer tracking lookups will be slow. `status` has no index for admin filtering by shipment status.

```php
// AFTER
$table->index('tracking_code');
$table->index('status');
```

---

### 16. orders: customer_phone Has No Length Limit in Migration

**File:** `2026_02_27_153902_create_orders_table.php:24`
**Impact:** `string('customer_phone')` defaults to VARCHAR(255). Phone numbers are max ~20 chars. Wasted storage and no implicit validation at DB level. Same for `customer_name` at VARCHAR(255).

```php
// AFTER
$table->string('customer_name', 150);
$table->string('customer_phone', 20);
```

---

### 17. landing_pages: product_id cascadeOnDelete

**File:** `2026_02_27_154527_create_landing_pages_table.php:18-20`
**Impact:** Deleting a product cascades to delete its landing page. Landing pages may have SEO value, external backlinks, and marketing spend. Silent deletion risks losing marketing assets.

```php
// AFTER
$table->foreignId('product_id')
    ->constrained()
    ->restrictOnDelete();
```

---

### 18. product_relations: No Unique Constraint — Duplicate Relations Possible

**File:** `2026_02_27_153805_create_product_relations_table.php`
**Impact:** No unique constraint on `(product_id, related_product_id, relation_type)`. The same cross-sell/upsell can be inserted multiple times, showing duplicate recommendations.

```php
// AFTER
$table->unique(['product_id', 'related_product_id', 'relation_type']);
```

---

### 19. combo_items: No Unique Constraint — Duplicate Variant in Combo

**File:** `2026_03_15_153527_create_combo_items_table.php`
**Impact:** No unique constraint on `(combo_id, product_variant_id)`. Same variant can be added to a combo multiple times as separate rows instead of incrementing quantity.

```php
// AFTER
$table->unique(['combo_id', 'product_variant_id']);
```

---

### 20. webhooks: secret Stored as Plain String

**File:** `2026_03_07_154330_create_webhooks_table.php:22`
**Impact:** Webhook `secret` is stored as a plain `string()`. If the DB is compromised, all webhook secrets are exposed. Should be encrypted at rest.

```php
// AFTER — use encrypted cast in model
// In Webhook model:
protected $casts = ['secret' => 'encrypted'];
```

---

### 21. No processing_at Timestamp on Orders

**File:** `2026_02_27_153902_create_orders_table.php`
**Impact:** Order status enum includes `processing` but there's no `processing_at` timestamp. The status transition to `processing` loses temporal data needed for fulfillment SLA tracking.

```php
// AFTER
$table->timestamp('processing_at')->nullable();
```

---

## Future Readiness Assessment

### 22. No Ledger/Transaction Log Table — Financial Auditability Gap

**Impact:** There is no immutable financial ledger. Order totals can be updated in place (`$order->update([...])`) with no history. For a referral/commission system, you need to trace: original amount, discount applied, coupon applied, commission owed, payout status. None of this is auditable from the current schema.

**Recommendation:** Create an `order_transactions` or `financial_ledger` table:
```php
Schema::create('order_transactions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('order_id')->constrained()->restrictOnDelete();
    $table->enum('type', ['charge', 'discount', 'coupon', 'shipping', 'refund', 'commission']);
    $table->decimal('amount', 12, 2);
    $table->string('description')->nullable();
    $table->json('metadata')->nullable();
    $table->timestamps();
});
```

---

### 23. No Referral/Commission Schema Hooks

**Impact:** The current schema has no:
- `referral_code` on users
- `referred_by` foreign key on users
- Commission tracking table
- Payout/withdrawal table

**Recommendation:** Plan these migrations now:
```php
// users table addition
$table->string('referral_code')->nullable()->unique();
$table->foreignId('referred_by')->nullable()->constrained('users')->nullOnDelete();

// New table
Schema::create('commissions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('order_id')->constrained();
    $table->foreignId('referrer_id')->constrained('users');
    $table->decimal('commission_amount', 10, 2);
    $table->enum('status', ['pending', 'approved', 'paid', 'cancelled']);
    $table->timestamps();
});
```

---

### 24. order_items: No SKU Snapshot

**File:** `2026_02_27_153938_create_order_items_table.php`
**Impact:** `product_name_snapshot` and `variant_title_snapshot` are captured, but `sku` is not. SKU is critical for warehouse operations, returns processing, and inventory reconciliation. If a variant's SKU changes, historical orders lose traceability.

```php
// AFTER
$table->string('sku_snapshot')->nullable();
```

---

## Summary

| Severity | Count | Key Themes |
|----------|-------|------------|
| Critical | 6 | Missing FK constraints on order_items, nullable stock columns, negative totals, unpopulated coupon_usages, coupon deletion destroys records |
| High | 6 | Cascade-deletes destroying products, missing financial snapshots, ghost carts, user deletion orphans orders |
| Medium | 12 | Missing indexes, no unique constraints, no length limits, no processing timestamp, webhook secrets plaintext, no ledger table |
| **Total** | **24** | |

**Top 5 Priorities:**
1. Add FK constraints + indexes on `order_items.product_id` and `order_items.variant_id` (Issue #1, #2)
2. Change `stock`/`reserved_stock` to `unsignedInteger()->default(0)` (Issue #3)
3. Change `cascadeOnDelete` to `restrictOnDelete` on category→products and coupon→orders (Issues #6, #7)
4. Populate `coupon_usages` table in OrderService (Issue #5)
5. Add discount/price snapshots to order_items for financial auditability (Issue #8)
