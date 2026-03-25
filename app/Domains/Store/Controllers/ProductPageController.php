<?php

namespace App\Domains\Store\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\Product\Models\Product;

class ProductPageController extends Controller
{
    public function show(string $slug)
    {
        $product = Product::query()
            ->select([
                'id',
                'slug',
                'landing_slug',
                'is_landing_enabled'
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

        /**
         * Otherwise load dynamic product blade
         * JS will call API: /api/v1/products/{slug}
         */
        return view('store.product', [
            'productSlug' => $product->slug,
            'productId' => $product->id
        ]);
    }
}
