<?php

namespace App\Domains\Product\Controllers;

use App\Domains\Product\Models\Product;
use App\Domains\Product\Resources\ProductLandingResource;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;

class ProductLandingController extends Controller
{
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
}
