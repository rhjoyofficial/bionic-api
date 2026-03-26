# Bionic API - Product Module Technical Audit

**Auditor:** Senior Staff Engineer (Architecture Review)
**Date:** 2026-03-26
**Branch:** `rhjoyOfficial`
**Scope:** Product Domain only (`app/Domains/Product/`, related routes, migrations)

---

## 1. High-Level Architecture Summary

The Product module follows a DDD-style layout:

```
app/Domains/Product/
├── Controllers/        (7 controllers)
├── Models/             (4 models)
├── Services/           (4 services)
├── Requests/           (3 form requests)
├── Resources/          (4 API resources)
└── Repositories/       (1 file — empty)
```

**Sub-features covered:**
- Admin CRUD (create, update, delete, list)
- Public listing & detail by slug
- Full-text search with filters
- Tier pricing per variant
- Upsell / Cross-sell relations
- Landing page product API
- Recommendation endpoint
- Pricing calculation service

**Database tables:** `products`, `product_variants`, `product_tier_prices`, `product_relations`

---

## 2. Issues Found

---

### ISSUE #1 — Route Shadowing: `/products/search` is UNREACHABLE

**Impact:** Critical
**File:** `routes/public.php:25-26,48`

The search endpoint is registered AFTER the `{slug}` wildcard route. Laravel matches top-down, so `GET /products/search` will be captured by `GET /products/{slug}` — the `PublicProductController@show` method will look for a product with `slug = "search"` and return 404.

**Before (broken):**
```php
// routes/public.php
Route::get('/products', [PublicProductController::class, 'index']);       // line 25
Route::get('/products/{slug}', [PublicProductController::class, 'show']); // line 26 — catches everything

// ... 22 lines later ...
Route::get('/products/search', [ProductSearchController::class, 'search']); // line 48 — NEVER REACHED
```

**After (fixed):**
```php
Route::get('/products', [PublicProductController::class, 'index']);
Route::get('/products/search', [ProductSearchController::class, 'search']); // BEFORE the wildcard
Route::get('/products/{slug}', [PublicProductController::class, 'show']);
Route::get('/products/{id}/recommendations', [ProductRecommendationController::class, 'show']);
```

---

### ISSUE #2 — ProductRelationService writes wrong column name (`type` vs `relation_type`)

**Impact:** Critical
**File:** `app/Domains/Product/Services/ProductRelationService.php:11-14`

The `product_relations` migration defines the column as `relation_type` (enum: `cross_sell`, `upsell`, `downsell`). The service writes to a column named `type`, which does not exist. Every call to `addRelation()` will throw a SQL error in production.

**Before (broken):**
```php
// ProductRelationService.php:11-14
return ProductRelation::create([
    'product_id' => $productId,
    'related_product_id' => $relatedId,
    'type' => $type                        // column does not exist
]);
```

**After (fixed):**
```php
return ProductRelation::create([
    'product_id' => $productId,
    'related_product_id' => $relatedId,
    'relation_type' => $type
]);
```

Additionally, the **Product model** `upsells()` and `crossSells()` use `wherePivot('type', ...)` which should be `wherePivot('relation_type', ...)`:

**Before (broken):**
```php
// Product.php:64
->wherePivot('type', 'upsell');
// Product.php:74
->wherePivot('type', 'cross_sell');
```

**After (fixed):**
```php
->wherePivot('relation_type', 'upsell');
->wherePivot('relation_type', 'cross_sell');
```

---

### ISSUE #3 — ProductService stores image as `image` but model/DB column is `thumbnail`

**Impact:** Critical
**File:** `app/Domains/Product/Services/ProductService.php:19-21,40-43,76`

The `StoreProductRequest` validates a field called `thumbnail`. The Product model's `$fillable` contains `thumbnail`. The DB column is `thumbnail`. But `ProductService` reads and writes `$data['image']` — a key that will never exist in the validated data.

**Before (broken):**
```php
// ProductService.php:19-21 (create)
if (isset($data['image'])) {
    $data['image'] = $data['image']->store($this->path, 'public');
}

// ProductService.php:40-43 (update)
if (isset($data['image'])) {
    if ($product->image) Storage::disk('public')->delete($product->image);
    $data['image'] = $data['image']->store($this->path, 'public');
}

// ProductService.php:76 (delete)
if ($product->image) Storage::disk('public')->delete($product->image);
```

**After (fixed):**
```php
// create
if (isset($data['thumbnail'])) {
    $data['thumbnail'] = $data['thumbnail']->store($this->path, 'public');
}

// update
if (isset($data['thumbnail'])) {
    if ($product->thumbnail) Storage::disk('public')->delete($product->thumbnail);
    $data['thumbnail'] = $data['thumbnail']->store($this->path, 'public');
}

// delete
if ($product->thumbnail) Storage::disk('public')->delete($product->thumbnail);
```

---

### ISSUE #4 — Product update DESTROYS all variants (and their tier prices, cart refs, order refs)

**Impact:** Critical
**File:** `app/Domains/Product/Services/ProductService.php:50-53`

On every update that includes variants, the service hard-deletes ALL existing variants and recreates them. Because `product_variants` has `cascadeOnDelete` on `product_tier_prices`, all tier pricing data is permanently lost. Worse, any `cart_items` or `order_items` referencing those variant IDs now point to non-existent rows.

**Before (destructive):**
```php
// ProductService.php:50-53
if ($variants !== null) {
    $product->variants()->delete();          // destroys all variant IDs
    $product->variants()->createMany($variants); // creates new IDs
}
```

**After (safe — sync with upsert):**
```php
if ($variants !== null) {
    $existingIds = [];

    foreach ($variants as $variantData) {
        if (isset($variantData['id'])) {
            $product->allVariants()->where('id', $variantData['id'])->update($variantData);
            $existingIds[] = $variantData['id'];
        } else {
            $new = $product->allVariants()->create($variantData);
            $existingIds[] = $new->id;
        }
    }

    // Only delete variants that were explicitly removed
    $product->allVariants()->whereNotIn('id', $existingIds)->delete();
}
```

---

### ISSUE #5 — UpdateProductRequest forces `variants` to be required and SKU uniqueness fails on own records

**Impact:** High
**File:** `app/Domains/Product/Requests/UpdateProductRequest.php:28-33`

When updating a product, `variants` is `required|array|min:1` — so you cannot update just the product name without also re-submitting all variants. Additionally, `variants.*.sku` has `unique:product_variants,sku` with no exclusion for the product's own existing SKUs, so re-submitting existing variants will fail validation.

**Before (broken):**
```php
// UpdateProductRequest.php:28-30
'variants' => 'required|array|min:1',
'variants.*.sku' => 'required|string|unique:product_variants,sku',
'landing_slug' => 'nullable|string|unique:products,landing_slug',
```

**After (fixed):**
```php
'variants' => 'sometimes|array|min:1',
'variants.*.id' => 'nullable|integer|exists:product_variants,id',
'variants.*.sku' => [
    'required', 'string',
    Rule::unique('product_variants', 'sku')->ignore(
        $this->input('variants.*.id'), 'id'
    ),
],
'landing_slug' => [
    'nullable', 'string',
    Rule::unique('products', 'landing_slug')->ignore($this->route('product')),
],
```

> Note: Per-item unique ignore with `.*` is tricky in Laravel. A pragmatic alternative is to validate SKU uniqueness inside the service layer instead of the form request.

---

### ISSUE #6 — `ProductRelationController::destroy()` has no input validation

**Impact:** High
**File:** `app/Domains/Product/Controllers/ProductRelationController.php:30-39`

The `destroy` method reads `$request->product_id` and `$request->related_product_id` directly with zero validation. If either is null or invalid, the service call either silently does nothing or deletes unexpected rows.

**Before (no validation):**
```php
// ProductRelationController.php:30-39
public function destroy(Request $request)
{
    $this->service->removeRelation(
        $request->product_id,        // could be null
        $request->related_product_id  // could be null
    );
    return response()->json(['message' => 'Relation removed']);
}
```

**After (validated):**
```php
public function destroy(Request $request)
{
    $request->validate([
        'product_id' => 'required|exists:products,id',
        'related_product_id' => 'required|exists:products,id',
    ]);

    $this->service->removeRelation(
        $request->product_id,
        $request->related_product_id
    );

    return ApiResponse::success(null, 'Relation removed');
}
```

---

### ISSUE #7 — `PricingService::calculate()` can produce negative totals

**Impact:** High
**File:** `app/Domains/Product/Services/PricingService.php:34-38`

A fixed-value tier discount could exceed the line total if misconfigured (e.g., discount_value = 500 on a $10 item). No floor at zero.

**Before (can go negative):**
```php
// PricingService.php:40-44
return [
    'unit_price' => $basePrice,
    'discount' => $discount,
    'total' => $total - $discount    // can be negative
];
```

**After (safe):**
```php
return [
    'unit_price' => $basePrice,
    'discount' => min($discount, $total),
    'total' => max(0, $total - $discount),
];
```

---

### ISSUE #8 — `ProductVariant::getDiscountPercentAttribute()` division by zero

**Impact:** High
**File:** `app/Domains/Product/Models/ProductVariant.php:80`

If a variant has `discount_type = 'fixed'` and `price = 0`, the computation `$this->discount_value / $this->price` produces a division-by-zero error.

**Before (crashes on zero price):**
```php
// ProductVariant.php:80
return (int)round(($this->discount_value / $this->price) * 100);
```

**After (safe):**
```php
if ($this->price <= 0) return null;
return (int)round(($this->discount_value / $this->price) * 100);
```

---

### ISSUE #9 — Debug test route exposed in production API

**Impact:** High
**File:** `routes/api.php:9-16`

A raw closure route `/api/v1/test-pricing/{variant}/{qty}` is registered with no auth, no middleware, no rate limit. It exposes internal pricing logic and variant data to anyone. It also directly instantiates the service instead of using DI.

**Before (exposed):**
```php
// routes/api.php:9-16
Route::get('/test-pricing/{variant}/{qty}', function (
    \App\Domains\Product\Models\ProductVariant $variant,
    $qty
) {
    $service = new \App\Domains\Product\Services\PricingService();
    return $service->calculate($variant, (int)$qty);
});
```

**After (remove or guard):**
```php
// DELETE this route entirely, or at minimum:
if (app()->environment('local', 'testing')) {
    Route::get('/test-pricing/{variant}/{qty}', function (
        \App\Domains\Product\Models\ProductVariant $variant,
        $qty,
        \App\Domains\Product\Services\PricingService $service
    ) {
        return $service->calculate($variant, (int)$qty);
    });
}
```

---

### ISSUE #10 — `ProductSearchService` searches `description` column but `LIKE` on `longText` is slow and unindexed

**Impact:** Medium
**File:** `app/Domains/Product/Services/ProductSearchService.php:22-25`

The `description` column is `longText`. A `LIKE '%term%'` query on `longText` forces a full table scan and cannot use any index. At scale this will be a major bottleneck.

**Before:**
```php
// ProductSearchService.php:22-25
$q->where('name', 'LIKE', $searchTerm)
    ->orWhere('description', 'LIKE', $searchTerm);
```

**After (immediate improvement):**
```php
$q->where('name', 'LIKE', $searchTerm)
    ->orWhere('short_description', 'LIKE', $searchTerm);
```

> `short_description` is `text` (much smaller) and more relevant for search. Long-term: use MySQL fulltext index or a dedicated search engine (Meilisearch/Algolia).

---

### ISSUE #11 — Missing index on `product_variants.product_id`

**Impact:** Medium
**File:** `database/migrations/2026_02_27_153731_create_product_variants_table.php`

The `product_variants` table uses `foreignId('product_id')->constrained()`, which creates a foreign key but **Laravel only auto-creates an index for foreign keys on some DB drivers**. There is no explicit composite index for the common query pattern `WHERE product_id = ? AND is_active = true`.

**After (add migration):**
```php
$table->index(['product_id', 'is_active']);
```

---

### ISSUE #12 — Missing unique constraint on `product_tier_prices(variant_id, min_quantity)`

**Impact:** Medium
**File:** `database/migrations/2026_02_27_153804_create_product_tier_prices_table.php`

The `ProductTierPriceController` uses `updateOrCreate` keyed on `min_quantity`, which is good at the app level. But without a DB-level unique constraint, a race condition between two concurrent requests could create duplicate tier entries for the same variant+quantity.

**After (add to migration):**
```php
$table->unique(['variant_id', 'min_quantity']);
```

---

### ISSUE #13 — `ProductLandingResource` missing `final_price`, `available_stock`, and tier data for variants

**Impact:** Medium
**File:** `app/Domains/Product/Resources/ProductLandingResource.php:33-40`

The landing page resource returns a stripped-down variant with only `id`, `title`, `price`. This means the landing page (designed for conversions) shows no discounts, no stock status, and no tier pricing — unlike the normal product page. This is a business logic inconsistency.

**Before (incomplete):**
```php
// ProductLandingResource.php:33-40
'variants' => $this->variants->map(function ($v) {
    return [
        'id' => $v->id,
        'title' => $v->title,
        'price' => $v->price
    ];
}),
```

**After (consistent with ProductVariantResource):**
```php
'variants' => $this->variants->map(function ($v) {
    return [
        'id' => $v->id,
        'title' => $v->title,
        'sku' => $v->sku,
        'price' => (float) $v->price,
        'final_price' => (float) $v->final_price,
        'discount_percent' => $v->discount_percent,
        'available_stock' => $v->available_stock,
    ];
}),
```

Also, `ProductLandingController` eager-loads `variants` but not `variants.tierPrices`, so accessing tier data would N+1. Fix:
```php
->with(['variants.tierPrices', 'category'])
```

---

### ISSUE #14 — `ProductRecommendationController` returns raw JSON, not `ApiResponse`

**Impact:** Medium
**File:** `app/Domains/Product/Controllers/ProductRecommendationController.php:16-19`

Every other controller uses the standardized `ApiResponse::success()` wrapper. This endpoint returns a bare `response()->json()`, breaking the API contract for frontend consumers.

**Before (inconsistent):**
```php
return response()->json([
    'upsells' => ProductResource::collection($product->upsells),
    'cross_sells' => ProductResource::collection($product->crossSells)
]);
```

**After (consistent):**
```php
return ApiResponse::success([
    'upsells' => ProductResource::collection($product->upsells),
    'cross_sells' => ProductResource::collection($product->crossSells)
]);
```

---

### ISSUE #15 — `ProductResource` accesses `category` without guaranteed eager loading (N+1)

**Impact:** Medium
**File:** `app/Domains/Product/Resources/ProductResource.php:19-22`

The resource always accesses `$this->category?->id` and `$this->category?->name`. In `PublicProductController@index`, the query eager-loads `variants.tierPrices` but NOT `category`. Each product in the paginated list triggers a separate category query.

**Before (N+1):**
```php
// PublicProductController.php:15-19
$products = Product::query()
    ->where('is_active', true)
    ->with(['variants.tierPrices'])   // category NOT loaded
    ->latest()
    ->paginate(12);
```

**After (fixed):**
```php
$products = Product::query()
    ->where('is_active', true)
    ->with(['variants.tierPrices', 'category'])
    ->latest()
    ->paginate(12);
```

Same issue in `AdminProductController@index` (loads `variants` but not `category`).

---

### ISSUE #16 — Admin product `store` and `update` missing route-level permission middleware

**Impact:** Medium
**File:** `routes/admin.php:28-31`

Only the `GET products` route has `->middleware('permission:product.view')`. The `POST`, `PUT`, and `DELETE` routes rely solely on the FormRequest `authorize()` method. If someone bypasses the FormRequest (e.g., via a future code change), there's no route-level safety net for create/update/delete.

**Before:**
```php
Route::get('products', ...)->middleware('permission:product.view');
Route::post('products', ...);                // no permission middleware
Route::put('products/{product}', ...);       // no permission middleware
Route::delete('products/{product}', ...);    // no permission middleware
```

**After (defense in depth):**
```php
Route::get('products', ...)->middleware('permission:product.view');
Route::post('products', ...)->middleware('permission:product.create');
Route::put('products/{product}', ...)->middleware('permission:product.update');
Route::delete('products/{product}', ...)->middleware('permission:product.delete');
```

---

### ISSUE #17 — `ProductLandingController` has no error handling for missing landing pages

**Impact:** Low
**File:** `app/Domains/Product/Controllers/ProductLandingController.php:13-16`

`firstOrFail()` will throw a `ModelNotFoundException` that returns Laravel's default HTML 404 (not JSON) when called via the API layer, because there is no try/catch returning `ApiResponse::error()`.

**Before:**
```php
public function show($slug)
{
    $product = Product::where('landing_slug', $slug)
        ->where('is_landing_enabled', true)
        ->with(['variants', 'category'])
        ->firstOrFail();

    return new ProductLandingResource($product);
}
```

**After:**
```php
public function show($slug)
{
    $product = Product::where('landing_slug', $slug)
        ->where('is_landing_enabled', true)
        ->with(['variants.tierPrices', 'category'])
        ->first();

    if (!$product) {
        return ApiResponse::error('Landing page not found', null, 404);
    }

    return ApiResponse::success(new ProductLandingResource($product));
}
```

---

### ISSUE #18 — `ProductRepository.php` is an empty file (dead code)

**Impact:** Low
**File:** `app/Domains/Product/Repositories/ProductRepository.php`

The file is empty. It occupies space and implies an unused abstraction layer. Either implement it or remove it.

**Recommendation:** Delete the file unless a repository pattern will be adopted project-wide.

---

## 3. Issue Summary Table

| # | Issue | Severity | Category |
|---|-------|----------|----------|
| 1 | `/products/search` route shadowed by `{slug}` wildcard | **Critical** | Routing Bug |
| 2 | `ProductRelationService` writes `type` instead of `relation_type` | **Critical** | Logic Bug |
| 3 | `ProductService` uses `image` key instead of `thumbnail` | **Critical** | Logic Bug |
| 4 | Product update destroys all variants (breaks cart/order refs) | **Critical** | Data Integrity |
| 5 | `UpdateProductRequest` forces variants, SKU unique fails on self | **High** | Validation Flaw |
| 6 | `ProductRelationController::destroy()` has no validation | **High** | Security |
| 7 | `PricingService` can return negative totals | **High** | Business Logic |
| 8 | `ProductVariant::discountPercent` division by zero | **High** | Runtime Crash |
| 9 | Debug test-pricing route exposed in production | **High** | Security |
| 10 | `LIKE` search on `longText` description column | **Medium** | Performance |
| 11 | Missing index on `product_variants(product_id, is_active)` | **Medium** | Performance |
| 12 | Missing unique constraint on tier prices | **Medium** | Data Integrity |
| 13 | Landing page variants missing price/stock/discount data | **Medium** | API Contract |
| 14 | Recommendation endpoint breaks `ApiResponse` contract | **Medium** | API Contract |
| 15 | N+1 query: category not eager-loaded in product listings | **Medium** | Performance |
| 16 | Admin product CUD routes missing permission middleware | **Medium** | Security |
| 17 | Landing controller returns HTML 404 instead of JSON | **Low** | API Contract |
| 18 | Empty `ProductRepository.php` dead code | **Low** | Dead Code |

---

## 4. Product Module Maturity Assessment

| Aspect | Score (0-10) | Notes |
|--------|:---:|-------|
| DDD Structure | 8 | Clean separation: Controllers, Services, Models, Requests, Resources |
| API Design | 5 | Broken search route, inconsistent response wrappers, landing gap |
| Validation | 5 | Good on create, broken on update (SKU unique, required variants) |
| Business Logic | 6 | Pricing service is solid; relation service has column name bug |
| Data Integrity | 3 | Variant delete-and-recreate is destructive; no unique on tiers |
| Performance | 5 | Good eager loading on some paths; N+1 on others; LIKE on longText |
| Security | 5 | Auth present but incomplete middleware, debug route exposed |
| SPA Readiness | 6 | Resources are well-structured; landing page contract incomplete |

**Overall Product Module Score: 5.5 / 10**

---

## 5. Merge Recommendation

### NEEDS FIXES BEFORE MERGE

The Product module has **4 Critical** and **5 High** severity issues. Merging this as-is will result in:
- Search endpoint completely non-functional
- Product relations completely non-functional (wrong column name)
- Thumbnail uploads silently failing (wrong field name)
- Data corruption on every product update (variants destroyed)
- Division-by-zero crashes on free/zero-price variants
- Exposed debug endpoint in production

---

## 6. Required Fix Checklist (Before Merge)

### Critical (must fix)
- [ ] Move `/products/search` route ABOVE `/products/{slug}` in `routes/public.php`
- [ ] Fix `ProductRelationService` to use `relation_type` instead of `type`
- [ ] Fix `Product` model `upsells()`/`crossSells()` to use `wherePivot('relation_type', ...)`
- [ ] Fix `ProductService` to use `thumbnail` instead of `image` in create/update/delete
- [ ] Replace variant delete-and-recreate with upsert logic in `ProductService::update()`

### High (should fix)
- [ ] Make `variants` field `sometimes` in `UpdateProductRequest`
- [ ] Add SKU unique-ignore logic for update validation
- [ ] Add input validation to `ProductRelationController::destroy()`
- [ ] Add `max(0, ...)` floor to `PricingService::calculate()` total
- [ ] Guard against division-by-zero in `ProductVariant::getDiscountPercentAttribute()`
- [ ] Remove or env-guard the `/test-pricing/` debug route

### Medium (should fix before production traffic)
- [ ] Change search to use `short_description` instead of `longText description`
- [ ] Add `->with('category')` to `PublicProductController@index` and `AdminProductController@index`
- [ ] Add composite index on `product_variants(product_id, is_active)`
- [ ] Add unique constraint on `product_tier_prices(variant_id, min_quantity)`
- [ ] Complete `ProductLandingResource` variant data (final_price, stock, discount)
- [ ] Wrap recommendation response in `ApiResponse::success()`
- [ ] Add permission middleware to admin product POST/PUT/DELETE routes
- [ ] Add JSON error handling to `ProductLandingController`

### Low (cleanup)
- [ ] Delete empty `ProductRepository.php`

---

*End of Product Module Audit*
