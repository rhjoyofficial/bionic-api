<?php

namespace App\Domains\Landing\Controllers;

use App\Domains\Landing\Models\LandingPage;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Domains\Landing\Resources\LandingPageResource;

/**
 * AdminLandingPageController
 *
 * Full CRUD for managing landing pages from the admin panel.
 */
class AdminLandingPageController extends Controller
{
    /**
     * List all landing pages with optional filters.
     */
    public function index(Request $request): JsonResponse
    {
        $query = LandingPage::query()
            ->with(['product:id,name,thumbnail', 'combo:id,name,image']);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $pages = $query->latest()->paginate($request->integer('per_page', 15));

        return ApiResponse::paginated(LandingPageResource::collection($pages));
    }

    /**
     * Show a single landing page with relationships.
     */
    public function show(LandingPage $landingPage): JsonResponse
    {
        $landingPage->load([
            'product:id,name,thumbnail',
            'combo:id,name,image',
            'items.variant.product:id,name',
            'items.combo:id,name',
        ]);

        return ApiResponse::success(new LandingPageResource($landingPage), 'Landing page retrieved');
    }

    /**
     * Create a new landing page.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'slug'              => 'required|string|max:100|unique:landing_pages,slug',
            'type'              => 'required|in:product,combo,sales',
            'product_id'        => 'nullable|required_if:type,product|integer|exists:products,id',
            'combo_id'          => 'nullable|required_if:type,combo|integer|exists:combos,id',
            'title'             => 'required|string|max:200',
            'hero_image'        => 'nullable|string|max:500',
            'blade_template'    => 'nullable|string|max:100',
            'content'           => 'nullable|string',
            'meta_title'        => 'nullable|string|max:200',
            'meta_description'  => 'nullable|string|max:500',
            'pixel_event_name'  => 'nullable|string|max:100',
            'config'            => 'nullable|array',
            'config.free_delivery_amount' => 'nullable|numeric|min:0',
            'config.free_delivery_qty'    => 'nullable|integer|min:1',
            'is_active'         => 'boolean',
            // Sales items (only for type=sales)
            'items'                      => 'nullable|array',
            'items.*.product_variant_id' => 'nullable|integer|exists:product_variants,id',
            'items.*.combo_id'           => 'nullable|integer|exists:combos,id',
            'items.*.is_preselected'     => 'boolean',
            'items.*.sort_order'         => 'integer|min:0',
        ]);

        try {
            return DB::transaction(function () use ($validated) {
                $landing = LandingPage::create([
                    'slug'             => $validated['slug'],
                    'type'             => $validated['type'],
                    'product_id'       => $validated['product_id'] ?? null,
                    'combo_id'         => $validated['combo_id'] ?? null,
                    'title'            => $validated['title'],
                    'hero_image'       => $validated['hero_image'] ?? null,
                    'blade_template'   => $validated['blade_template'] ?? $validated['type'] . '-default',
                    'content'          => $validated['content'] ?? null,
                    'meta_title'       => $validated['meta_title'] ?? null,
                    'meta_description' => $validated['meta_description'] ?? null,
                    'pixel_event_name' => $validated['pixel_event_name'] ?? null,
                    'config'           => $validated['config'] ?? null,
                    'is_active'        => $validated['is_active'] ?? false,
                ]);

                // Create sales items if type is sales
                if ($validated['type'] === 'sales' && !empty($validated['items'])) {
                    foreach ($validated['items'] as $i => $item) {
                        $landing->items()->create([
                            'product_variant_id' => $item['product_variant_id'] ?? null,
                            'combo_id'           => $item['combo_id'] ?? null,
                            'is_preselected'     => $item['is_preselected'] ?? false,
                            'sort_order'         => $item['sort_order'] ?? $i,
                        ]);
                    }
                }

                $landing->load(['product:id,name', 'combo:id,name', 'items']);

                return ApiResponse::success($landing, 'Landing page created', 201);
            });
        } catch (Exception $e) {
            Log::error('Failed to create landing page: ' . $e->getMessage());
            return ApiResponse::error('Failed to create landing page.', null, 500);
        }
    }

    /**
     * Update an existing landing page.
     */
    public function update(Request $request, LandingPage $landingPage): JsonResponse
    {
        $validated = $request->validate([
            'slug'              => ['sometimes', 'string', 'max:100', Rule::unique('landing_pages', 'slug')->ignore($landingPage->id)],
            'type'              => 'sometimes|in:product,combo,sales',
            'product_id'        => 'nullable|integer|exists:products,id',
            'combo_id'          => 'nullable|integer|exists:combos,id',
            'title'             => 'sometimes|string|max:200',
            'hero_image'        => 'nullable|string|max:500',
            'blade_template'    => 'nullable|string|max:100',
            'content'           => 'nullable|string',
            'meta_title'        => 'nullable|string|max:200',
            'meta_description'  => 'nullable|string|max:500',
            'pixel_event_name'  => 'nullable|string|max:100',
            'config'            => 'nullable|array',
            'config.free_delivery_amount' => 'nullable|numeric|min:0',
            'config.free_delivery_qty'    => 'nullable|integer|min:1',
            'is_active'         => 'boolean',
            // Sales items
            'items'                      => 'nullable|array',
            'items.*.product_variant_id' => 'nullable|integer|exists:product_variants,id',
            'items.*.combo_id'           => 'nullable|integer|exists:combos,id',
            'items.*.is_preselected'     => 'boolean',
            'items.*.sort_order'         => 'integer|min:0',
        ]);

        try {
            return DB::transaction(function () use ($request, $validated, $landingPage) {

                $landingData = collect($validated)->except('items')->toArray();
                $landingPage->update($landingData);

                if (!empty($validated['items'])) {
                    foreach ($validated['items'] as $i => $item) {
                        $landingPage->items()->create([
                            'product_variant_id' => $item['product_variant_id'] ?? null,
                            'combo_id'           => $item['combo_id'] ?? null,
                            'is_preselected'     => $item['is_preselected'] ?? false,
                            'sort_order'         => $item['sort_order'] ?? $i,
                        ]);
                    }
                }

                $landingPage->load(['product:id,name', 'combo:id,name', 'items']);
                return ApiResponse::success($landingPage, 'Landing page updated');
            });
        } catch (Exception $e) {
            Log::error('Failed to update landing page: ' . $e->getMessage());
            return ApiResponse::error('Failed to update landing page.', null, 500);
        }
    }

    /**
     * Delete a landing page.
     */
    public function destroy(LandingPage $landingPage): JsonResponse
    {
        try {
            DB::transaction(function () use ($landingPage) {
                $landingPage->items()->delete();
                $landingPage->delete();
            });

            return ApiResponse::success(null, 'Landing page deleted');
        } catch (Exception $e) {
            Log::error('Failed to delete landing page: ' . $e->getMessage());
            return ApiResponse::error('Failed to delete landing page.', null, 500);
        }
    }

    /**
     * Toggle active status.
     */
    public function toggleActive(LandingPage $landingPage): JsonResponse
    {
        $landingPage->update(['is_active' => !$landingPage->is_active]);

        return ApiResponse::success(
            ['is_active' => $landingPage->is_active],
            $landingPage->is_active ? 'Landing page activated' : 'Landing page deactivated'
        );
    }
}
