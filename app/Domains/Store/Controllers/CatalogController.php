<?php

namespace App\Domains\Store\Controllers;

use App\Domains\Category\Models\Category;
use App\Domains\Product\Models\Product;
use App\Domains\Product\Models\ProductVariant;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CatalogController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::query()
            ->active()
            ->with(['variants.tierPrices', 'category']);

        // Category filter — resolved once, used for both the query and the view variable
        $selectedCategory = null;
        if ($request->filled('category')) {
            $selectedCategory = Category::query()
                ->active()
                ->where('slug', $request->query('category'))
                ->first();

            if ($selectedCategory) {
                $query->where('category_id', $selectedCategory->id);
            }
        }

        // Search
        if ($request->filled('q')) {
            $term = '%' . trim((string) $request->query('q')) . '%';
            $query->where(function (Builder $search) use ($term) {
                $search->where('name', 'like', $term)
                    ->orWhere('short_description', 'like', $term);
            });
        }

        $this->applyPriceFilter($query, $request);
        $this->applyStockFilter($query, $request);
        $this->applySorting($query, $request);

        $products   = $query->paginate(12)->withQueryString();
        $priceRange = $this->getPriceRange();

        return view('store.pages.products', [
            'products'         => $products,
            'selectedCategory' => $selectedCategory,
            'searchQuery'      => $request->query('q', ''),
            'priceRange'       => $priceRange,
        ]);
    }

    public function category(string $slug, Request $request): View
    {
        $selectedCategory = Category::query()
            ->active()
            ->where('slug', $slug)
            ->firstOrFail();

        $query = Product::query()
            ->active()
            ->with(['variants.tierPrices', 'category'])
            ->where('category_id', $selectedCategory->id);

        // Search still works within a category
        if ($request->filled('q')) {
            $term = '%' . trim((string) $request->query('q')) . '%';
            $query->where(function (Builder $search) use ($term) {
                $search->where('name', 'like', $term)
                    ->orWhere('short_description', 'like', $term);
            });
        }

        $this->applyPriceFilter($query, $request);
        $this->applyStockFilter($query, $request);
        $this->applySorting($query, $request);

        // withQueryString() ensures pagination links preserve ?sort=, ?price_min=, etc.
        $products   = $query->paginate(12)->withQueryString();
        $priceRange = $this->getPriceRange();

        return view('store.pages.products', [
            'products'         => $products,
            'selectedCategory' => $selectedCategory,
            'searchQuery'      => $request->query('q', ''),
            'priceRange'       => $priceRange,
        ]);
    }

    // ─── Private Helpers ─────────────────────────────────────────────────────

    /**
     * Filter by price range using the cheapest active variant's base price.
     * Note: we filter on `price` (the stored column) not `final_price` (a PHP
     * accessor) because SQL cannot evaluate PHP computed attributes.
     */
    private function applyPriceFilter(Builder $query, Request $request): void
    {
        $priceMin = (int) $request->query('price_min', 0);
        $priceMax = (int) $request->query('price_max', 0);

        if ($priceMin <= 0 && $priceMax <= 0) {
            return;
        }

        $query->whereHas('variants', function (Builder $q) use ($priceMin, $priceMax) {
            $q->where('is_active', true);

            if ($priceMin > 0) {
                $q->where('price', '>=', $priceMin);
            }
            if ($priceMax > 0) {
                $q->where('price', '<=', $priceMax);
            }
        });
    }

    /**
     * Optionally restrict to products with at least 1 unit of available stock.
     */
    private function applyStockFilter(Builder $query, Request $request): void
    {
        if ($request->boolean('in_stock')) {
            $query->whereHas('variants', function (Builder $q) {
                $q->where('is_active', true)
                    ->whereRaw('(stock - reserved_stock) > 0');
            });
        }
    }

    /**
     * Apply sort order. Defaults to newest first.
     * Price sorts use a correlated subquery on product_variants.
     */
    private function applySorting(Builder $query, Request $request): void
    {
        $sort = $request->query('sort', 'latest');

        if ($sort === 'price_asc') {
            $query->orderBy(
                ProductVariant::select('price')
                    ->whereColumn('product_id', 'products.id')
                    ->where('is_active', true)
                    ->orderBy('price')
                    ->limit(1),
                'asc'
            );
            return;
        }

        if ($sort === 'price_desc') {
            $query->orderBy(
                ProductVariant::select('price')
                    ->whereColumn('product_id', 'products.id')
                    ->where('is_active', true)
                    ->orderByDesc('price')
                    ->limit(1),
                'desc'
            );
            return;
        }

        // 'latest' (default) and any unknown value fall through to newest first
        $query->latest();
    }

    /**
     * Return the actual min and max prices from active variants.
     * Cached for 6 hours — invalidate via ProductVariant observer if needed.
     */
    private function getPriceRange(): array
    {
        return Cache::remember('catalog:price_range', now()->addHours(6), function () {
            $min = (int) ProductVariant::where('is_active', true)->min('price');
            $max = (int) ProductVariant::where('is_active', true)->max('price');

            return [
                'min' => max(0, $min),
                'max' => $max > 0 ? $max : 10000,
            ];
        });
    }
}
