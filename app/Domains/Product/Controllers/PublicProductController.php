<?php

namespace App\Domains\Product\Controllers;

use App\Domains\Product\Models\Product;
use App\Domains\Product\Resources\ProductResource;
use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PublicProductController extends Controller
{
    public function index()
    {
        $products = Product::query()
            ->where('is_active', true)
            ->with(['variants.tierPrices'])
            ->latest()
            ->paginate(12);

        return ApiResponse::paginated(ProductResource::collection($products));
    }

    public function show(string $slug)
    {
        try {
            $product = Product::query()
                ->where('slug', $slug)
                ->where('is_active', true)
                ->with(['variants.tierPrices'])
                ->firstOrFail();

            return ApiResponse::success(new ProductResource($product));
        } catch (ModelNotFoundException) {
            return ApiResponse::error('Product not found or unavailable', null, 404);
        }
    }
}
