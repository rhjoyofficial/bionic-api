<?php

namespace App\Domains\Product\Controllers;

use App\Domains\Product\Requests\ProductSearchRequest;
use App\Domains\Product\Resources\ProductResource;
use App\Domains\Product\Services\ProductSearchService;
use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Support\Facades\Log;

class ProductSearchController extends Controller
{
    public function __construct(
        private ProductSearchService $service
    ) {}

    public function search(ProductSearchRequest $request)
    {
        try {
            $products = $this->service->search($request->validated());

            return ApiResponse::paginated(
                ProductResource::collection($products)
            );
        } catch (\Exception $e) {
            Log::error("Search failed: " . $e->getMessage());
            return ApiResponse::error('Search failed', null, 500);
        }
    }
}
