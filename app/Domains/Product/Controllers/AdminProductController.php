<?php

namespace App\Domains\Product\Controllers;

use App\Domains\Product\Models\Product;
use App\Domains\Product\Requests\StoreProductRequest;
use App\Domains\Product\Requests\UpdateProductRequest;
use App\Domains\Product\Resources\ProductResource;
use App\Domains\Product\Services\ProductService;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Log;

class AdminProductController extends Controller
{
    use AuthorizesRequests;
    public function __construct(private ProductService $service) {}

    public function index()
    {
        return ProductResource::collection(
            Product::with('variants')->latest()->paginate(10)
        );
    }

    public function store(StoreProductRequest $request)
    {
        try {
            $product = $this->service->create($request->validated());
            return (new ProductResource($product))
                ->additional(['success' => true, 'message' => 'Product created successfully'])
                ->response()->setStatusCode(201);
        } catch (Exception $e) {
            return $this->handleError($e, 'Product creation failed');
        }
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        try {
            $this->authorize('product.update');
            $updated = $this->service->update($product, $request->validated());
            return (new ProductResource($updated))
                ->additional(['success' => true, 'message' => 'Product updated successfully']);
        } catch (Exception $e) {
            return $this->handleError($e, 'Product update failed');
        }
    }

    public function destroy(Product $product)
    {
        $this->authorize('product.delete');

        $this->service->delete($product);

        return response()->json(['message' => 'Deleted successfully']);
    }

    private function handleError(Exception $e, string $msg)
    {
        Log::error($msg . ': ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => $msg,
            'error' => config('app.debug') ? $e->getMessage() : 'Server Error'
        ], 500);
    }
}
