<?php

namespace App\Domains\Product\Controllers;

use App\Domains\Product\Models\Product;
use App\Domains\Product\Requests\StoreProductRequest;
use App\Domains\Product\Requests\UpdateProductRequest;
use App\Domains\Product\Resources\ProductResource;
use App\Domains\Product\Services\ProductService;
use App\Http\Controllers\Controller;
use App\Helpers\ApiResponse; // Import your new helper
use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Log;

class AdminProductController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private ProductService $service) {}

    public function index()
    {
        $this->authorize('product.view');

        $products = Product::with(['variants', 'category'])
            ->when(request('q'), fn($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->when(request('category_id'), fn($q, $id) => $q->where('category_id', $id))
            ->when(request('status'), fn($q, $status) => $q->where('is_active', $status === 'active'))
            ->latest()
            ->paginate(15);

        return ApiResponse::paginated(ProductResource::collection($products));
    }

    public function show(Product $product)
    {
        $this->authorize('product.view');

        $product->load(['allVariants.tierPrices', 'category']);

        return ApiResponse::success(new ProductResource($product), 'Product loaded');
    }

    public function store(StoreProductRequest $request)
    {
        try {
            $product = $this->service->create($request->validated());

            return ApiResponse::success(
                new ProductResource($product),
                'Product created successfully',
                201
            );
        } catch (Exception $e) {
            return $this->handleError($e, 'Product creation failed');
        }
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        try {
            $this->authorize('product.update');

            $updated = $this->service->update($product, $request->validated());

            return ApiResponse::success(
                new ProductResource($updated),
                'Product updated successfully'
            );
        } catch (Exception $e) {
            return $this->handleError($e, 'Product update failed');
        }
    }

    public function destroy(Product $product)
    {
        try {
            $this->authorize('product.delete');

            $this->service->delete($product);

            return ApiResponse::success(null, 'Product deleted successfully');
        } catch (Exception $e) {
            return $this->handleError($e, 'Product deletion failed');
        }
    }

    /**
     * Updated to use standard ApiResponse
     */
    private function handleError(Exception $e, string $msg)
    {
        Log::error($msg . ': ' . $e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);

        return ApiResponse::error(
            $msg,
            config('app.debug') ? $e->getMessage() : null,
            500
        );
    }
}
