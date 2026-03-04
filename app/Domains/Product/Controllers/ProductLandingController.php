<?php

namespace App\Domains\Product\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\Product\Models\Product;
use App\Domains\Product\Resources\ProductLandingResource;

class ProductLandingController extends Controller
{
    public function show($slug)
    {
        $product = Product::where('landing_slug', $slug)
            ->where('is_landing_enabled', true)
            ->with(['variants', 'category'])
            ->firstOrFail();

        return new ProductLandingResource($product);
    }
}
