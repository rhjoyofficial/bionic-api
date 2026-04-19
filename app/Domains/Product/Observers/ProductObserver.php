<?php

namespace App\Domains\Product\Observers;

use App\Domains\Product\Models\Product;
use Illuminate\Support\Facades\Cache;

class ProductObserver
{
    private function clearCache(Product $product): void
    {
        Cache::forget("product:page:{$product->slug}");
        Cache::forget("product:api:{$product->slug}");
        Cache::forget('home:trending_products');
        Cache::forget('home:category_products');

        if ($product->landing_slug) {
            Cache::forget("landing:meta:{$product->landing_slug}");
            Cache::forget("landing:data:{$product->landing_slug}:product");
        }
    }

    public function created(Product $product): void { $this->clearCache($product); }
    public function updated(Product $product): void { $this->clearCache($product); }
    public function deleted(Product $product): void { $this->clearCache($product); }
}
