<?php

namespace App\Domains\Product\Observers;

use App\Domains\Product\Models\ProductVariant;
use Illuminate\Support\Facades\Cache;

class ProductVariantObserver
{
    private function clearCache(ProductVariant $variant): void
    {
        $product = $variant->product;

        if (! $product) {
            return;
        }

        Cache::forget("product:page:{$product->slug}");
        Cache::forget("product:api:{$product->slug}");
        Cache::forget('home:trending_products');
        Cache::forget('home:category_products');

        if ($product->landing_slug) {
            Cache::forget("landing:meta:{$product->landing_slug}");
            Cache::forget("landing:data:{$product->landing_slug}:product");
        }
    }

    public function updated(ProductVariant $variant): void { $this->clearCache($variant); }
    public function deleted(ProductVariant $variant): void { $this->clearCache($variant); }
}
