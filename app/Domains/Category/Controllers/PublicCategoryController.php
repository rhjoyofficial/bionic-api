<?php

namespace App\Domains\Category\Controllers;

use App\Domains\Category\Models\Category;
use App\Domains\Category\Resources\CategoryResource;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PublicCategoryController extends Controller
{
    /**
     * Display a listing of active categories for the store menu/sidebar.
     */
    public function index()
    {
        try {
            $categories = Cache::remember('api:categories:active', now()->addHours(24), fn () =>
                Category::where('is_active', true)->orderBy('sort_order', 'asc')->get()
            );

            return ApiResponse::success(
                CategoryResource::collection($categories),
                'Categories retrieved successfully'
            );
        } catch (Exception $e) {
            Log::error('Public Category Index Error: ' . $e->getMessage());

            return ApiResponse::error(
                'Unable to load categories',
                config('app.debug') ? $e->getMessage() : null,
                500
            );
        }
    }
}
