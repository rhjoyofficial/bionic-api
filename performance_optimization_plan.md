# Bionic — Performance Optimization & Cache Strategy Plan

> **Analysis based on actual codebase audit.** Every finding, fix, and cache key below is grounded in the real files, controllers, models, and routes of this project.

---

## 1. Performance Bottleneck Areas

### 1.1 Database / Query Layer

| Location | Problem | Impact |
|---|---|---|
| `HomeController::index()` | 3 separate queries (categories, trending products, category products, combos). `Product::active()->trending()->with(['variants.tierPrices'])` fetches all N products, then Eloquent does M tier-price queries inside. | HIGH — every homepage load |
| `CatalogController::index()` | `with(['variants.tierPrices', 'category'])` — correct, but paginating 12 products still issues N sub-queries for `tierPrices` when variant count is high. | MEDIUM |
| `ProductPageController::show()` | Loads `upsells` and `crossSells` both as separate BelongsToMany with nested eager loads — potentially 4–6 queries for one page. Then immediately checks `LandingPage::where('slug', ...)->exists()` which is another query. | HIGH |
| `LandingPageController::show()` (Blade) | `ShippingZone::where('is_active', true)->get()` fires on every single landing page render with zero caching. | MEDIUM |
| `ViewServiceProvider` | `View::composer('*', ...)` fires on **every** view render including partials and admin views. One cache miss on any admin view would trigger a DB query mid-request. | MEDIUM |
| `PublicCategoryController::index()` | Raw `Category::where('is_active', true)->orderBy('sort_order')->get()` — no caching, called on every API request from the frontend SPA/Blade. | HIGH — hit on every page |
| `CartService::addItem()` | Correct use of `lockForUpdate()`. No N+1 issues found here. ✅ | OK |
| `CartService::syncCartPrices()` | Loads `items.variant.tierPrices` — fine for small carts, but iterates all items in a loop recalculating pricing. | LOW-MEDIUM |

### 1.2 Blade Rendering

- `HomeController` passes `ProductResource::collection(...)` to Blade. The `ProductResource::toArray()` resolves `image_url` via `asset('storage/...')` — safe, but the Resource transformer runs per-product, per-request.
- `ProductPageController` loads `certifications`, `upsells`, `crossSells` with deeply nested eager loads. The product page is likely one of the heaviest Blade renders.
- The `ViewServiceProvider` uses `View::composer('*', ...)` — this binds to **every** view render including layouts and partials, adding overhead.

### 1.3 API Layer

- `GET /api/categories` — hits DB on every request (no cache).
- `GET /api/products` — hits DB on every request (no cache).
- `GET /api/products/{slug}` — hits DB on every request (no cache).
- `GET /api/landing/{slug}` — hits DB (LandingPage + Product + ShippingZones) on every request.
- All API responses are JSON — no HTTP response caching headers set.

### 1.4 Frontend / Vite

- Vite config bundles `app.css`, `app.js`, and `admin.js` — good separation.
- No code splitting configured for the customer JS bundle.
- No image optimization pipeline exists (images uploaded directly via `store()` to `public` disk as-is).
- TailwindCSS is included via `@tailwindcss/vite` — this is the v4 JIT approach, which is correct and already fast.

### 1.5 What Is Already Good ✅

- `CartService` properly uses `DB::transaction()` and `lockForUpdate()` — safe and correct.
- `ProductVariant::availableStock` is a virtual Eloquent attribute, not a DB query.
- `ViewServiceProvider` already caches `global_categories` for 6 hours — partially implemented.
- Sanctum token auth + rate limiting on all write routes — correct.
- `QUEUE_CONNECTION=database` — jobs run async, so order processing doesn't block the web request.

---

## 2. Optimization Strategy

### Layer 1 — Database (Highest ROI)

1. **Ensure proper indexes exist** on frequently filtered columns.
2. **Fix N+1 in HomeController** — merge category products into one query with a group.
3. **Fix N+1 in ProductPageController** — the `LandingPage::exists()` check can be eliminated for non-landing products.
4. **Add read-through caching** to all public endpoints that are read-heavy and change rarely.

### Layer 2 — Laravel Cache (High ROI, Safe)

Cache **read-only, public data** that changes only when an admin performs an action:

| Data | Cache Key | TTL | Invalidated When |
|---|---|---|---|
| All active categories (for nav/sidebar) | `categories:active` | 24h | Category created/updated/deleted/toggled |
| Home page trending products | `home:trending_products` | 6h | Product updated/toggled/created/deleted |
| Home page "more products" | `home:category_products` | 6h | Product updated/toggled/created/deleted |
| Home page combos | `home:combos` | 6h | Combo updated/toggled/created/deleted |
| Home page hero banners | `home:hero_banners` | 24h | HeroBanner updated/deleted |
| Landing page data (by slug) | `landing:product:{slug}` | 2h | LandingPage updated/toggled, or Product updated |
| Shipping zones list | `shipping:zones:active` | 24h | ShippingZone created/updated/deleted |
| Single product page | `product:page:{slug}` | 2h | Product updated/toggled |
| Public API categories | `api:categories:active` | 24h | Category mutated |
| Public API products listing | `api:products:page:{hash}` | 30min | Any product mutated |

### Layer 3 — HTTP Response Headers

Add `Cache-Control` headers to Blade pages with static content:
- Landing pages: `Cache-Control: public, max-age=300` (5 minutes) — acceptable staleness window.
- Product pages: `Cache-Control: private, no-cache` — user-specific data (pricing/stock can vary by login state).
- Cart/Checkout: `Cache-Control: no-store` — **never cache**.

### Layer 4 — Frontend

- Add `loading="lazy"` to all `<img>` tags below the fold.
- Use Vite's `build.rollupOptions.manualChunks` to split vendor JS.
- Compress images on upload using PHP's `GD` or `Intervention/Image`.

---

## 3. Caching Design

### 3.1 Cache Driver

**Use Redis** (already configured in `.env` with `REDIS_CLIENT=phpredis`). Change `CACHE_STORE=database` to `CACHE_STORE=redis` in both `.env` and `.env.production`.

```
# .env change
CACHE_STORE=redis
```

> **Why Redis over database cache?**  
> The project already has Redis configured but uses the database as cache store — this means cache reads/writes go to MySQL, which defeats the purpose. Redis is in-memory and orders of magnitude faster for cache operations.

### 3.2 Cache Key Naming Convention

Use structured tag-style keys even without Redis Tags (for compatibility):

```
{domain}:{entity}:{identifier}
```

Examples:
- `categories:active`
- `product:page:bionic-protein-bar`
- `landing:product:bionic-protein-bar-landing`
- `home:trending_products`
- `api:products:page:listings_p1`

### 3.3 What Should Be Cached

```
✅ SAFE TO CACHE:
- Active categories list
- Trending products
- Hero banners
- Active shipping zones
- Public product listings (paginated)
- Single product page data
- Landing page data (product/combo/sales)
- Active combos list

❌ NEVER CACHE:
- Cart contents (always user/session-specific)
- Checkout pricing (must always be live — critical for correctness)
- Order data
- Stock numbers at checkout time (lockForUpdate handles this)
- Coupon validation logic
- Session data
- Reserved stock calculations
```

---

## 4. Cache Invalidation Strategy

### 4.1 The Pattern — Model Observers

The safest, cleanest approach is **Eloquent Model Observers**. When an admin saves a product, the observer automatically fires `Cache::forget()`. This is:
- **Automatic** — no controller changes needed.
- **Correct** — fires on every write path (API, direct DB via tinker, etc.).
- **Maintainable** — mutation logic and cache logic stay separate.

### 4.2 Observer Map

#### `ProductObserver`
Fires on: `created`, `updated`, `deleted`

```php
// Clears:
Cache::forget("product:page:{$product->slug}");
Cache::forget("landing:product:{$product->landing_slug}"); // if set
Cache::forget('home:trending_products');
Cache::forget('home:category_products');
Cache::tags(['products'])->flush(); // if using Redis tags
// Also: pattern-delete api:products:page:* (all paginated API pages)
```

**Critical addition**: When price OR stock is changed, also call `Cache::forget()` for any related landing page cache. The variant's `price`, `discount_value`, `sale_ends_at`, `stock` updates must also trigger this.

#### `ProductVariantObserver`
Fires on: `updated` (price/stock changes come through here)

```php
// The variant belongs to a product — invalidate that product's cache
$product = $variant->product; // no N+1 risk, already loaded or single query
Cache::forget("product:page:{$product->slug}");
Cache::forget("home:trending_products");
Cache::forget("home:category_products");
// Clear landing page if product has one
if ($product->landing_slug) {
    Cache::forget("landing:product:{$product->landing_slug}");
}
```

#### `CategoryObserver`
Fires on: `created`, `updated`, `deleted`

```php
Cache::forget('categories:active');
Cache::forget('api:categories:active');
```

#### `ComboObserver`
Fires on: `created`, `updated`, `deleted`

```php
Cache::forget('home:combos');
// If combo has an associated landing page:
Cache::forget("landing:combo:{$combo->id}");
```

#### `HeroBannerObserver`
Fires on: `created`, `updated`, `deleted`

```php
Cache::forget('home:hero_banners');
```

#### `LandingPageObserver`
Fires on: `updated`, `deleted` (including `toggleActive`)

```php
Cache::forget("landing:product:{$landingPage->slug}");
Cache::forget("landing:combo:{$landingPage->slug}");
Cache::forget("landing:sales:{$landingPage->slug}");
```

#### `ShippingZoneObserver`
Fires on: `created`, `updated`, `deleted`

```php
Cache::forget('shipping:zones:active');
```

### 4.3 Invalidation Timing — Immediate vs Queued

| Scenario | Approach |
|---|---|
| Category name/image change | Immediate `Cache::forget()` in observer |
| Product price/discount change | Immediate `Cache::forget()` in `ProductVariantObserver` |
| Product stock change | **Do NOT cache stock** — stock is always read live |
| Landing page toggle on/off | Immediate `Cache::forget()` in `LandingPageObserver` |
| Bulk operations (future) | Queue a `ClearProductCacheJob` to avoid blocking the request |

> **Why not use queued cache invalidation?** For this size of project, synchronous `Cache::forget()` is safer — there's no risk of stale data appearing between when admin saves and when the job runs. Queue-based invalidation is only beneficial if cache warming (not just busting) is needed.

### 4.4 Stale Data Prevention Rules

1. **Stock is NEVER cached.** The variant's `available_stock` is always calculated from live DB data (`stock - reserved_stock`).
2. **Checkout pricing is NEVER cached.** `CheckoutPricingService` and `PricingService::calculate()` always read live price + tier prices. This is already how the code works — maintain it.
3. **Cart snapshots are price-at-time-of-add** via `unit_price_snapshot`. The `syncCartPrices()` method recalculates — call this on cart view, not just on checkout.
4. **Landing page checkout uses live pricing** via `LandingCheckoutController` — do not add caching to the `preview` or `checkout` endpoints.

---

## 5. Sync Strategy (Admin ↔ Customer)

### The Core Problem

Admin updates a product price → cache still has old price → customer sees wrong price.

### Solution: Write-Through Invalidation (not TTL-based)

Do **not** rely solely on TTL for correctness. TTL (`Cache::remember(..., now()->addHours(2), ...)`) is the *fallback safety net*, not the primary mechanism. The primary mechanism is **observer-based immediate invalidation**.

```
Admin updates Product
    → AdminProductController::update()
    → ProductService::update()
    → Product::save()
    → ProductObserver::updated() fires automatically
    → Cache::forget("product:page:{$slug}")
    → Cache::forget("home:trending_products")
    → Next customer request → cache miss → fresh DB query → re-cached
```

This guarantees **maximum 0-second staleness** (data is correct immediately after admin save).

### Data Consistency Matrix

| Admin Action | What Gets Invalidated | Customer Sees Correct Data? |
|---|---|---|
| Update product name/description | `product:page:{slug}` | ✅ Immediately |
| Change product price (via variant) | `product:page:{slug}`, `home:trending_products`, `landing:*` | ✅ Immediately |
| Change stock (via variant update) | Stock is never cached | ✅ Always live |
| Toggle product active/inactive | All product caches | ✅ Immediately |
| Create/edit/delete category | `categories:active`, `api:categories:active` | ✅ Immediately |
| Update combo price | `home:combos`, `landing:combo:*` | ✅ Immediately |
| Deactivate landing page | `landing:*:{slug}` | ✅ Immediately (redirect stops) |
| Update shipping zone | `shipping:zones:active` | ✅ Immediately |

---

## 6. Step-by-Step Implementation Plan

### Phase 1 — Quick Wins (1–2 days, zero risk)

#### Step 1.1 — Switch Cache Driver to Redis

**File:** `.env` and `.env.production`

```diff
-CACHE_STORE=database
+CACHE_STORE=redis
```

**Why:** Redis is already configured but unused for cache. This alone gives a massive speed boost to the existing `global_categories` cache in `ViewServiceProvider`.

---

#### Step 1.2 — Fix `ViewServiceProvider` Scope

**File:** `app/Providers/ViewServiceProvider.php`

The current `View::composer('*', ...)` fires on every view including admin views and sub-partials. Narrow it to store views:

```php
View::composer(['store.*', 'layouts.*', 'components.*'], function ($view) {
    if (!$view->getData()['globalCategories'] ?? false) {
        $view->with('globalCategories', Cache::remember(
            'categories:active',
            now()->addHours(24),
            fn() => Category::active()->ordered()->get()
        ));
    }
});
```

**Why:** Prevents the 6-hour cache key from running on admin panels where `$globalCategories` is never used.

---

#### Step 1.3 — Add Missing Database Indexes

**New migration file:** `add_performance_indexes.php`

```php
Schema::table('products', function (Blueprint $table) {
    $table->index(['is_active', 'is_trending']); // HomeController trending query
    $table->index(['is_active', 'category_id']); // CatalogController category filter
    $table->index(['is_active', 'created_at']);   // latest() ordering
});

Schema::table('product_variants', function (Blueprint $table) {
    $table->index(['product_id', 'is_active']); // variants() scope
});

Schema::table('landing_pages', function (Blueprint $table) {
    $table->index(['slug', 'is_active']); // LandingPageController show()
});

Schema::table('categories', function (Blueprint $table) {
    $table->index(['is_active', 'sort_order']); // ordered() scope
});

Schema::table('hero_banners', function (Blueprint $table) {
    $table->index(['is_active', 'sort_order']); // HomeController banners
});
```

**Why:** Composite indexes on `(is_active, <column>)` are essential because every public query filters by `is_active` first.

---

#### Step 1.4 — Add `loading="lazy"` to Images

In all product listing Blade templates, add `loading="lazy"` to `<img>` tags below the fold. The hero image and first visible product should NOT be lazy (they're above the fold).

**Files to update:**
- `resources/views/store/pages/` (all view partials with `<img>`)
- `resources/views/components/` (product cards, category cards)

---

### Phase 2 — Medium Optimizations (2–4 days)

#### Step 2.1 — Create Model Observers

**New files:**
- `app/Domains/Product/Observers/ProductObserver.php`
- `app/Domains/Product/Observers/ProductVariantObserver.php`
- `app/Domains/Category/Observers/CategoryObserver.php`
- `app/Domains/Product/Observers/ComboObserver.php`
- `app/Domains/Landing/Observers/LandingPageObserver.php`
- `app/Domains/Store/Observers/HeroBannerObserver.php`
- `app/Domains/Shipping/Observers/ShippingZoneObserver.php`

**Example — `ProductObserver.php`:**
```php
namespace App\Domains\Product\Observers;

use App\Domains\Product\Models\Product;
use Illuminate\Support\Facades\Cache;

class ProductObserver
{
    private function clearProductCache(Product $product): void
    {
        Cache::forget("product:page:{$product->slug}");
        Cache::forget('home:trending_products');
        Cache::forget('home:category_products');
        
        if ($product->landing_slug) {
            Cache::forget("landing:product:{$product->landing_slug}");
        }
        
        // Clear all paginated product API pages
        // Pattern delete requires Redis - use tags or prefix delete
        Cache::tags(['products'])->flush(); // only if Redis tags enabled
    }

    public function created(Product $product): void  { $this->clearProductCache($product); }
    public function updated(Product $product): void  { $this->clearProductCache($product); }
    public function deleted(Product $product): void  { $this->clearProductCache($product); }
}
```

**Register all observers in `AppServiceProvider::boot()`:**
```php
Product::observe(ProductObserver::class);
ProductVariant::observe(ProductVariantObserver::class);
Category::observe(CategoryObserver::class);
Combo::observe(ComboObserver::class);
LandingPage::observe(LandingPageObserver::class);
HeroBanner::observe(HeroBannerObserver::class);
ShippingZone::observe(ShippingZoneObserver::class);
```

---

#### Step 2.2 — Cache the HomeController Queries

**File:** `app/Domains/Store/Controllers/HomeController.php`

```php
public function index()
{
    $heroBanners = Cache::remember('home:hero_banners', now()->addHours(24), fn() =>
        HeroBanner::active()->ordered()->get()
    );

    $categories = Cache::remember('categories:active', now()->addHours(24), fn() =>
        Category::active()->ordered()->get()
    );

    $trendingProductsRaw = Cache::remember('home:trending_products', now()->addHours(6), fn() =>
        Product::query()->active()->trending()->with(['variants.tierPrices'])->limit(12)->get()
    );

    $categoryProductsRaw = Cache::remember('home:category_products', now()->addHours(6), fn() =>
        Product::active()->with(['variants.tierPrices', 'category'])->latest()->limit(20)->get()
    );

    $combos = Cache::remember('home:combos', now()->addHours(6), fn() =>
        Combo::where('is_active', true)->with(['items.variant.product'])->latest()->limit(12)->get()
    );

    // ... rest unchanged
}
```

**Why:** The homepage is the highest-traffic page. These 4 queries run on every single homepage load. Caching them for 6 hours with observer-based invalidation means a product change invalidates the cache immediately.

---

#### Step 2.3 — Cache the `PublicCategoryController` & `PublicProductController`

**File:** `app/Domains/Category/Controllers/PublicCategoryController.php`

```php
$categories = Cache::remember('api:categories:active', now()->addHours(24), fn() =>
    Category::where('is_active', true)->orderBy('sort_order')->get()
);
```

**File:** `app/Domains/Product/Controllers/PublicProductController.php`

```php
public function show(string $slug)
{
    $product = Cache::remember("product:api:{$slug}", now()->addHours(2), function () use ($slug) {
        return Product::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->with(['category', 'variants.tierPrices'])
            ->firstOrFail();
    });
    // ...
}
```

> ⚠️ **Do NOT cache `PublicProductController::index()`** (the paginated listing) unless you use Redis Tags that can be flushed on product mutation. Paginated queries depend on `?page=` parameters and any product change would require clearing all pages. Instead, keep this uncached but ensure the index query is fast via proper indexing from Step 1.3.

---

#### Step 2.4 — Cache Landing Pages

**File:** `app/Domains/Landing/Controllers/LandingPageController.php`

```php
public function show(string $slug)
{
    $landing = Cache::remember("landing:meta:{$slug}", now()->addMinutes(30), fn() =>
        LandingPage::where('slug', $slug)->where('is_active', true)->first()
    );

    if (!$landing) abort(404);

    $zones = Cache::remember('shipping:zones:active', now()->addHours(24), fn() =>
        ShippingZone::where('is_active', true)->orderBy('sort_order')
            ->get(['id', 'name', 'base_charge', 'free_shipping_threshold'])
    );

    $cacheKey = "landing:data:{$slug}:{$landing->type}";
    $data = Cache::remember($cacheKey, now()->addHours(2), function () use ($landing) {
        return match ($landing->type) {
            LandingPage::TYPE_PRODUCT => $this->buildProductData($landing),
            LandingPage::TYPE_COMBO   => $this->buildComboData($landing),
            LandingPage::TYPE_SALES   => $this->buildSalesData($landing),
            default                   => [],
        };
    });

    return view($landing->resolveView(), array_merge($data, [
        'landing' => $landing,
        'zones'   => $zones,
    ]));
}
```

**Why:** Landing pages are typically high-traffic conversion pages run in campaigns. Every uncached render hits Product + Variant + TierPrices + ShippingZone queries.

---

#### Step 2.5 — Optimize `ProductPageController`

**File:** `app/Domains/Store/Controllers/ProductPageController.php`

Move the `LandingPage::exists()` check inside the cache to avoid an extra query when serving from cache:

```php
public function show(string $slug)
{
    $product = Cache::remember("product:page:{$slug}", now()->addHours(2), fn() =>
        Product::query()
            ->with([
                'category',
                'variants.tierPrices',
                'certifications',
                'upsells' => fn($query) => $query->active()->with(['variants.tierPrices', 'category']),
                'crossSells' => fn($query) => $query->active()->with(['variants.tierPrices', 'category']),
            ])
            ->active()
            ->where('slug', $slug)
            ->firstOrFail()
    );

    // Landing page redirect check (1 lightweight existence query, NOT cached)
    if ($product->is_landing_enabled && $product->landing_slug) {
        $landingExists = LandingPage::where('slug', $product->landing_slug)
            ->where('is_active', true)
            ->exists();

        if ($landingExists) {
            return redirect()->route('landing.page', ['slug' => $product->landing_slug]);
        }
    }
    // ...
}
```

---

### Phase 3 — Advanced Optimizations (Optional, 3–5 days)

#### Step 3.1 — Image Compression on Upload

Install Intervention Image v3:
```bash
composer require intervention/image
```

**Modify `ProductService::create()`** to compress thumbnails on upload:
```php
use Intervention\Image\Laravel\Facades\Image;

// After storing thumbnail:
$path = $data['thumbnail']->store($this->path, 'public');
$fullPath = Storage::disk('public')->path($path);
Image::read($fullPath)->scaleDown(800, 800)->toWebp(85)->save($fullPath);
$data['thumbnail'] = $path;
```

Apply same to gallery and category images.

**Why:** Uncompressed product images can be 2–5MB. WebP at 85% quality is visually identical but 60–80% smaller.

---

#### Step 3.2 — Vite Bundle Splitting

**File:** `vite.config.js`

```js
export default defineConfig({
    plugins: [...],
    build: {
        rollupOptions: {
            output: {
                manualChunks: {
                    'vendor': ['alpinejs'], // separate Alpine.js chunk
                }
            }
        }
    }
});
```

**Why:** The customer bundle (`app.js`) and admin bundle (`admin.js`) already separated is the most impactful split. Alpine.js can be chunked separately so browsers can cache it independently.

---

#### Step 3.3 — Add HTTP Cache Headers to Blade Responses

Create a middleware `SetCacheHeaders.php`:
```php
class SetPublicCacheHeaders
{
    public function handle(Request $request, Closure $next, int $maxAge = 300): Response
    {
        $response = $next($request);
        
        if ($request->isMethod('GET') && $response->getStatusCode() === 200) {
            $response->headers->set('Cache-Control', "public, max-age={$maxAge}, stale-while-revalidate=60");
        }
        
        return $response;
    }
}
```

Apply only to landing pages:
```php
Route::get('/product-page/{slug}', [LandingPageController::class, 'show'])
    ->middleware('cache.public:300')
    ->name('landing.page');
```

> ⚠️ **Do NOT apply to cart, checkout, account, or any authenticated pages.**

---

#### Step 3.4 — Optimize the `syncCartPrices()` Call

Currently `CartService::syncCartPrices()` is available but must be invoked manually. Call it at cart view time:

**File:** `app/Domains/Cart/Controllers/CartController.php` — in the `view()` method:
```php
$priceChanged = $this->cartService->syncCartPrices($cart);
$cart = $this->cartService->formatCartDetails($cart);

if ($priceChanged) {
    // Return a flag so the frontend can show "Prices updated" notice
    $cart['price_updated_notice'] = true;
}
```

**Why:** Prices may have changed between when a customer added to cart and when they check out. Syncing on cart view (not just checkout) gives the customer early price update notification.

---

## 7. Risks & Safety

### 7.1 What Can Go Wrong With Caching

| Risk | Scenario | Mitigation |
|---|---|---|
| **Stale price shown** | Admin changes price, cache not yet cleared | Observers invalidate immediately. TTL is only a backup. |
| **Stale stock shown** | Customer sees "in stock" but it's empty | **Never cache stock.** Always read `available_stock` live. |
| **Wrong user sees cached cart** | Cart cached incorrectly | Cart is **never cached** — always read from DB by session/user. |
| **Race condition on checkout** | Two users buy last item simultaneously | CartService already uses `lockForUpdate()` ✅ |
| **Cache stampede** | Cache expires, 1000 users hit DB simultaneously | Use `Cache::remember()` with atomic lock (available in Laravel 12 via `Cache::lock()`). For high-traffic pages, implement this if needed. |
| **Observer not firing** | Using `Product::query()->update()` (mass update bypasses observers) | Always use model instances for updates, not mass updates. Never do `Product::whereIn(...)->update([...])` for cache-sensitive fields. |

### 7.2 Fields That Should Never Be Cached

```
❌ NEVER CACHE THESE FIELDS/QUERIES:
- ProductVariant::stock
- ProductVariant::reserved_stock
- ProductVariant::available_stock
- Any checkout pricing query
- Cart item count / cart totals
- Coupon validation
- Order status
- User session data
- Order history
```

### 7.3 Observer Bypass Risk

The biggest silent risk: if any code uses Eloquent mass-update like this:

```php
// ⚠️ This BYPASSES observers:
Product::where('category_id', $oldId)->update(['category_id' => $newId]);
```

Observers only fire when you save a model **instance**, not via `::update()` or `::insert()`. Audit all bulk operations and ensure they either:
1. Use model instances (safe but slow for bulk), or
2. Manually call `Cache::forget()` after the bulk operation.

Current codebase appears to only use instance-based updates in services — this is correct and safe.

### 7.4 Checkout Is Always Safe (Verify This)

The `CheckoutPricingService` must **never** use a cache. Audit it to confirm:
- It reads `ProductVariant::find()` fresh.
- It reads `tierPrices` fresh.
- It does NOT use any cached product/variant model.

Currently `CheckoutPricingService` reads through `CartService` which reads from the database. ✅ Keep this.

---

## Summary — Priority Execution Order

```
Priority 1 (Do immediately — 0 risk):
  ✅ Switch CACHE_STORE from database to redis
  ✅ Add database indexes (separate migration)
  ✅ Add lazy loading to images

Priority 2 (This week — low risk):
  ✅ Create all 7 Model Observers + register in AppServiceProvider
  ✅ Cache HomeController queries
  ✅ Cache PublicCategoryController
  ✅ Cache LandingPageController
  ✅ Cache ProductPageController
  ✅ Fix ViewServiceProvider scope

Priority 3 (Next week — medium effort):
  ✅ Image compression via Intervention Image
  ✅ Vite bundle splitting
  ✅ Call syncCartPrices() on cart view
  ✅ HTTP cache headers for landing pages
```

> **Correctness rule:** When unsure whether to cache something, **do not cache it**. For this project, correctness of price/stock is more important than raw speed. The optimizations above target data that only changes via explicit admin actions — not transactional data.
