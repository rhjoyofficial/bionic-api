<?php

namespace App\Domains\Category\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\Category\Models\Category;
use App\Domains\Category\Services\CategoryService;
use App\Domains\Category\Requests\StoreCategoryRequest;
use App\Domains\Category\Requests\UpdateCategoryRequest;
use App\Domains\Category\Resources\CategoryResource;
use App\Helpers\ApiResponse; // Import your new helper
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Log;
use Exception;

class AdminCategoryController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private CategoryService $service) {}

    public function index()
    {
        try {
            $this->authorize('category.view');

            $perPage = min((int) request('per_page', 10), 100);

            $categories = Category::withCount('products')
                ->when(request('q'), fn($q, $search) => $q->where('name', 'like', "%{$search}%"))
                ->latest()
                ->paginate($perPage);

            return ApiResponse::paginated(CategoryResource::collection($categories));
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to retrieve categories');
        }
    }

    public function store(StoreCategoryRequest $request)
    {
        try {
            $category = $this->service->create($request->validated());

            return ApiResponse::success(
                new CategoryResource($category),
                'Category created successfully',
                201
            );
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to create category');
        }
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        try {
            $this->authorize('category.update');

            $updatedCategory = $this->service->update($category, $request->validated());

            return ApiResponse::success(
                new CategoryResource($updatedCategory),
                'Category updated successfully'
            );
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to update category');
        }
    }

    public function destroy(Category $category)
    {
        try {
            $this->authorize('category.delete');

            $this->service->delete($category);

            return ApiResponse::success(null, 'Category deleted successfully');
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to delete category');
        }
    }

    /**
     * Unified error response handler using ApiResponse
     */
    private function handleError(Exception $e, string $message)
    {
        Log::error($message . ': ' . $e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);

        return ApiResponse::error(
            $message,
            config('app.debug') ? $e->getMessage() : null,
            500
        );
    }
}
