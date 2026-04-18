<?php

namespace App\Domains\Store\Controllers;

use App\Domains\Store\Models\HeroBanner;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AdminHeroBannerController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $banners = HeroBanner::with(['product:id,name', 'category:id,name'])
                ->orderBy('sort_order')
                ->get()
                ->map(fn($b) => $this->format($b));

            return ApiResponse::success($banners);
        } catch (\Exception $e) {
            return $this->error($e, 'Failed to load hero banners');
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'title'       => 'required|string|max:255',
                'badge'       => 'nullable|string|max:100',
                'subtitle'    => 'nullable|string|max:255',
                'description' => 'nullable|string|max:2000',
                'button_text' => 'nullable|string|max:100',
                'button_url'  => 'nullable|string|max:500',
                'sort_order'  => 'nullable|integer|min:0',
                'is_active'   => 'boolean',
                'starts_at'   => 'nullable|date',
                'ends_at'     => 'nullable|date|after_or_equal:starts_at',
                'product_id'  => 'nullable|integer|exists:products,id',
                'category_id' => 'nullable|integer|exists:categories,id',
                'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            ]);

            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('banners', 'public');
            }

            $data['sort_order'] ??= HeroBanner::max('sort_order') + 1;
            $data['is_active'] ??= true;

            $banner = HeroBanner::create($data);

            return ApiResponse::success($this->format($banner->fresh(['product', 'category'])), 'Hero banner created.', 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::error('Validation failed', $e->errors(), 422);
        } catch (\Exception $e) {
            return $this->error($e, 'Failed to create banner');
        }
    }

    public function show(HeroBanner $heroBanner): JsonResponse
    {
        return ApiResponse::success($this->format($heroBanner->load(['product:id,name', 'category:id,name'])));
    }

    public function update(Request $request, HeroBanner $heroBanner): JsonResponse
    {
        try {
            $data = $request->validate([
                'title'       => 'sometimes|required|string|max:255',
                'badge'       => 'nullable|string|max:100',
                'subtitle'    => 'nullable|string|max:255',
                'description' => 'nullable|string|max:2000',
                'button_text' => 'nullable|string|max:100',
                'button_url'  => 'nullable|string|max:500',
                'sort_order'  => 'nullable|integer|min:0',
                'is_active'   => 'boolean',
                'starts_at'   => 'nullable|date',
                'ends_at'     => 'nullable|date|after_or_equal:starts_at',
                'product_id'  => 'nullable|integer|exists:products,id',
                'category_id' => 'nullable|integer|exists:categories,id',
                'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            ]);

            if ($request->hasFile('image')) {
                // Delete old image
                if ($heroBanner->image) {
                    Storage::disk('public')->delete($heroBanner->image);
                }
                $data['image'] = $request->file('image')->store('banners', 'public');
            }

            $heroBanner->update($data);

            return ApiResponse::success($this->format($heroBanner->fresh(['product', 'category'])), 'Hero banner updated.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::error('Validation failed', $e->errors(), 422);
        } catch (\Exception $e) {
            return $this->error($e, 'Failed to update banner');
        }
    }

    public function destroy(HeroBanner $heroBanner): JsonResponse
    {
        try {
            if ($heroBanner->image) {
                Storage::disk('public')->delete($heroBanner->image);
            }
            $heroBanner->delete();
            return ApiResponse::success(null, 'Hero banner deleted.');
        } catch (\Exception $e) {
            return $this->error($e, 'Failed to delete banner');
        }
    }

    public function toggleActive(HeroBanner $heroBanner): JsonResponse
    {
        try {
            $heroBanner->update(['is_active' => !$heroBanner->is_active]);
            return ApiResponse::success($this->format($heroBanner->fresh()), 'Status updated.');
        } catch (\Exception $e) {
            return $this->error($e, 'Failed to toggle status');
        }
    }

    private function format(HeroBanner $b): array
    {
        return [
            'id'          => $b->id,
            'badge'       => $b->badge,
            'title'       => $b->title,
            'subtitle'    => $b->subtitle,
            'description' => $b->description,
            'button_text' => $b->button_text,
            'button_url'  => $b->button_url,
            'image'       => $b->image,
            'image_url'   => $b->image ? asset('storage/' . $b->image) : null,
            'sort_order'  => $b->sort_order,
            'is_active'   => $b->is_active,
            'starts_at'   => $b->starts_at?->toDateTimeString(),
            'ends_at'     => $b->ends_at?->toDateTimeString(),
            'product_id'  => $b->product_id,
            'category_id' => $b->category_id,
            'product'     => $b->product ? ['id' => $b->product->id, 'name' => $b->product->name] : null,
            'category'    => $b->category ? ['id' => $b->category->id, 'name' => $b->category->name] : null,
            'created_at'  => $b->created_at?->toDateTimeString(),
        ];
    }

    private function error(\Exception $e, string $msg): JsonResponse
    {
        Log::error("{$msg}: {$e->getMessage()}");
        return ApiResponse::error($msg, config('app.debug') ? $e->getMessage() : null, 500);
    }
}
