<?php

namespace App\Domains\Store\Controllers;

use App\Domains\Landing\Models\LandingPage;
use App\Domains\Product\Models\Product;
use App\Http\Controllers\Controller;
use Illuminate\Support\Collection;

class ProductPageController extends Controller
{
    public function show(string $slug)
    {
        $product = Product::query()
            ->with([
                'category',
                'variants.tierPrices',
                'certifications',
                'upsells' => fn($query) => $query->active()->with(['variants.tierPrices', 'category']),
                'crossSells' => fn($query) => $query->active()->with(['variants.tierPrices', 'category']),
            ])
            ->active()
            ->where('slug', $slug)
            ->firstOrFail();

        /**
         * Safe landing-page redirect.
         *
         * We only redirect when the LandingPage row is confirmed to exist
         * AND is currently active. This prevents customer-facing 404s in
         * the (unlikely but possible) scenario where the two tables drift
         * out of sync — e.g., someone manually toggled is_active in the DB,
         * or a transaction partially failed.
         *
         * If the guard fails we fall through silently to the standard
         * product view — the customer sees a page, not an error.
         */
        if ($product->is_landing_enabled && $product->landing_slug) {
            $landingExists = LandingPage::where('slug', $product->landing_slug)
                ->where('is_active', true)
                ->exists();

            if ($landingExists) {
                return redirect()->route('landing.page', [
                    'slug' => $product->landing_slug,
                ]);
            }
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
