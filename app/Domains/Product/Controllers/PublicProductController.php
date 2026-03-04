<?php

namespace App\Domains\Product\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\Product\Models\Product;
use App\Domains\Product\Resources\ProductResource;
use App\Support\ApiResponse;
use Exception;

class PublicProductController extends Controller
{
    public function index()
    {
        try {
            $products = Product::where('is_active', true)
                ->with(['variants.tierPrices'])
                ->latest()
                ->paginate(12);

            return ApiResponse::paginated(ProductResource::collection($products));
        } catch (Exception $e) {
            return ApiResponse::error('Unable to fetch products', null, 500);
        }
    }

    public function show(string $slug)
    {
        try {
            $product = Product::where('slug', $slug)
                ->where('is_active', true) // Ensure inactive products aren't public
                ->with(['variants.tierPrices'])
                ->firstOrFail();

            return ApiResponse::success(new ProductResource($product));
        } catch (Exception $e) {
            return ApiResponse::error('Product not found or unavailable', null, 404);
        }
    }
}
