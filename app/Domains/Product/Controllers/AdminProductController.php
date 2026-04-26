<?php

namespace App\Domains\Product\Controllers;

use App\Domains\ActivityLog\Services\AdminLogger;
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
use Throwable;
use Illuminate\Http\Request;

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

            AdminLogger::log('products', "Product {$product->name} created", $product, [], 'created');

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

            AdminLogger::log('products', "Product {$product->name} updated", $product, [], 'updated');

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

            $name = $product->name;
            $this->service->delete($product);

            AdminLogger::log('products', "Product {$name} deleted", null, ['name' => $name], 'deleted');

            return ApiResponse::success(null, 'Product deleted successfully');
        } catch (Exception $e) {
            return $this->handleError($e, 'Product deletion failed');
        }
    }

    /**
     * Search directly from the Product model (no variants).
     */
    public function searchProducts(Request $request)
    {
        try {
            // The frontend uses ?q=
            $q = trim($request->get('q', ''));

            if (strlen($q) < 2) {
                return response()->json(['data' => []]);
            }

            $products = Product::select('id', 'name', 'sku', 'thumbnail')
                ->where('is_active', true)
                ->where(function ($query) use ($q) {
                    $query->where('name', 'like', "%{$q}%")
                        ->orWhere('sku', 'like', "%{$q}%");
                })
                ->limit(15)
                ->get();

            return response()->json(['data' => $products]);
        } catch (Throwable $e) {
            Log::error('Landing Page Product Search Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while searching for products.',
                'data' => []
            ], 500);
        }
    }

    public function toggleActive(Product $product)
    {
        try {
            $this->authorize('product.update');
            $updated = $this->service->toggleActiveStatus($product);

            AdminLogger::log('products', "Product {$updated->name} status changed to " . ($updated->is_active ? 'Active' : 'Inactive'), $updated, ['is_active' => $updated->is_active], 'status_changed');

            return ApiResponse::success(
                new ProductResource($updated),
                'Product status updated successfully'
            );
        } catch (Exception $e) {
            return $this->handleError($e, 'Product update failed');
        }
    }

    public function toggleLanding(Product $product, Request $request)
    {
        try {
            $this->authorize('product.update');

            $enabling = !$product->is_landing_enabled;

            // When enabling, a slug is required.
            if ($enabling) {
                $request->validate([
                    'landing_slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9\-]+$/'],
                ]);
            }

            $updated = $this->service->toggleLandingStatus(
                $product,
                $enabling ? $request->input('landing_slug') : null
            );

            AdminLogger::log('products', "Product {$updated->name} landing status changed to " . ($updated->is_landing_enabled ? 'Enabled' : 'Disabled'), $updated, ['is_landing_enabled' => $updated->is_landing_enabled], 'landing_status_changed');

            return ApiResponse::success(
                new ProductResource($updated),
                'Landing status updated successfully'
            );
        } catch (Exception $e) {
            return $this->handleError($e, 'Product update failed');
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
