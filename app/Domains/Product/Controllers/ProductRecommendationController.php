<?php

namespace App\Domains\Product\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\Product\Models\Product;
use App\Domains\Product\Resources\ProductResource;

class ProductRecommendationController extends Controller
{
    public function show($productId)
    {
        $product = Product::with(['upsells', 'crossSells'])
            ->findOrFail($productId);

        return response()->json([
            'upsells' => ProductResource::collection($product->upsells),
            'cross_sells' => ProductResource::collection($product->crossSells)
        ]);
    }
}
