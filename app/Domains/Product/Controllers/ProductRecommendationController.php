<?php

namespace App\Domains\Product\Controllers;

use App\Domains\Product\Models\Product;
use App\Domains\Product\Resources\ProductResource;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;

class ProductRecommendationController extends Controller
{
    public function show($productId)
    {
        $product = Product::with([
            'upsells' => fn($query) => $query->active()->with(['variants.tierPrices', 'category']),
            'crossSells' => fn($query) => $query->active()->with(['variants.tierPrices', 'category']),
        ])
            ->findOrFail($productId);

        return ApiResponse::success([
            'upsells' => ProductResource::collection($product->upsells),
            'cross_sells' => ProductResource::collection($product->crossSells)
        ]);
    }
}
