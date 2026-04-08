<?php

namespace App\Domains\Store\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\Product\Models\Product;
use Illuminate\Support\Collection;

class ProductPageController extends Controller
{
    public function show(string $slug)
    {
        $product = Product::query()
            ->with([
                'category',
                'variants.tierPrices',
                'upsells' => fn($query) => $query->active()->with(['variants.tierPrices', 'category']),
                'crossSells' => fn($query) => $query->active()->with(['variants.tierPrices', 'category']),
            ])
            ->active()
            ->where('slug', $slug)
            ->firstOrFail();

        /**
         * If landing enabled → redirect to landing PAGE
         * NOT landing API
         */
        if ($product->is_landing_enabled && $product->landing_slug) {
            return redirect()->route('landing.page', [
                'slug' => $product->landing_slug
            ]);
        }

        /** @var Collection<int, Product> $relatedProducts */
        $relatedProducts = $product->upsells
            ->merge($product->crossSells)
            ->unique('id')
            ->take(12)
            ->values();

        return view('store.product', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
        ]);
    }
}
