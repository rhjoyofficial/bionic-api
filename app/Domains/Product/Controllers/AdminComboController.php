<?php

namespace App\Domains\Product\Controllers;

use App\Domains\Product\Models\Combo;
use App\Domains\Product\Requests\StoreComboRequest;
use App\Domains\Product\Requests\UpdateComboRequest;
use App\Domains\Product\Resources\ComboResource;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminComboController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Combo::withCount('items');

            if ($q = $request->input('q')) {
                $query->where('title', 'like', "%{$q}%");
            }

            if ($request->filled('status')) {
                match ($request->input('status')) {
                    'active'   => $query->where('is_active', true),
                    'inactive' => $query->where('is_active', false),
                    'featured' => $query->where('is_featured', true),
                    default    => null,
                };
            }

            $combos = $query->with(['items.variant'])->latest()->paginate(15);

            return ApiResponse::paginated(ComboResource::collection($combos));
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to retrieve combos');
        }
    }

    public function show(Combo $combo): JsonResponse
    {
        try {
            $combo->load(['items.variant.product']);

            return ApiResponse::success(new ComboResource($combo));
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to retrieve combo');
        }
    }

    public function store(StoreComboRequest $request): JsonResponse
    {
        try {
            $combo = null;

            DB::transaction(function () use ($request, &$combo) {
                $data = $request->validated();

                // Resolve slug
                $data['slug'] = $this->uniqueSlug(
                    $data['slug'] ?? $data['title']
                );

                // Upload image
                if ($request->hasFile('image')) {
                    $data['image'] = $request->file('image')->store('combos', 'public');
                }

                $items = $data['items'];
                unset($data['items']);

                $combo = Combo::create($data);

                $this->syncItems($combo, $items);
            });

            $combo->load(['items.variant.product']);

            return ApiResponse::success(new ComboResource($combo), 'Combo created successfully', 201);
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to create combo');
        }
    }

    public function update(UpdateComboRequest $request, Combo $combo): JsonResponse
    {
        try {
            DB::transaction(function () use ($request, $combo) {
                $data = $request->validated();

                // Update slug if title changed and no explicit slug given
                if (isset($data['title']) && ! isset($data['slug'])) {
                    $data['slug'] = $this->uniqueSlug($data['title'], $combo->id);
                } elseif (isset($data['slug'])) {
                    $data['slug'] = $this->uniqueSlug($data['slug'], $combo->id);
                }

                // Replace image if new one uploaded
                if ($request->hasFile('image')) {
                    if ($combo->image) {
                        Storage::disk('public')->delete($combo->image);
                    }
                    $data['image'] = $request->file('image')->store('combos', 'public');
                }

                $items = $data['items'] ?? null;
                unset($data['items']);

                $combo->update($data);

                if ($items !== null) {
                    $this->syncItems($combo, $items);
                }
            });

            $combo->load(['items.variant.product']);

            return ApiResponse::success(new ComboResource($combo), 'Combo updated successfully');
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to update combo');
        }
    }

    public function destroy(Combo $combo): JsonResponse
    {
        try {
            if ($combo->image) {
                Storage::disk('public')->delete($combo->image);
            }

            $combo->delete(); // combo_items cascade

            return ApiResponse::success(null, 'Combo deleted successfully');
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to delete combo');
        }
    }

    public function toggleActive(Combo $combo): JsonResponse
    {
        try {
            $combo->update(['is_active' => ! $combo->is_active]);

            return ApiResponse::success([
                'id'        => $combo->id,
                'is_active' => $combo->is_active,
            ]);
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to toggle combo status');
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Delete all existing items for a combo and re-insert the given list.
     */
    private function syncItems(Combo $combo, array $items): void
    {
        $combo->items()->delete();

        foreach ($items as $item) {
            $combo->items()->create([
                'product_variant_id' => $item['variant_id'],
                'quantity'           => max(1, (int) $item['quantity']),
            ]);
        }
    }

    /**
     * Generate a URL-safe, unique slug from the given base string.
     */
    private function uniqueSlug(string $base, ?int $excludeId = null): string
    {
        $slug = Str::slug($base);
        $candidate = $slug;
        $i = 1;

        while (
            Combo::where('slug', $candidate)
                ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
                ->exists()
        ) {
            $candidate = $slug . '-' . $i++;
        }

        return $candidate;
    }

    private function handleError(Exception $e, string $msg, int $code = 500): JsonResponse
    {
        Log::error($msg . ': ' . $e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);

        return ApiResponse::error(
            $msg,
            config('app.debug') ? $e->getMessage() : null,
            $code
        );
    }
}
