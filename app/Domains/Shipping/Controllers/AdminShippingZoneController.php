<?php

namespace App\Domains\Shipping\Controllers;

use App\Domains\Shipping\Models\ShippingZone;
use App\Domains\Shipping\Requests\ReorderShippingZonesRequest;
use App\Domains\Shipping\Requests\StoreShippingZoneRequest;
use App\Domains\Shipping\Requests\UpdateShippingZoneRequest;
use App\Domains\Shipping\Resources\ShippingZoneResource;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminShippingZoneController extends Controller
{
    /**
     * Return all zones ordered by sort_order (no pagination — typically ≤30 zones).
     */
    public function index(): JsonResponse
    {
        try {
            $zones = ShippingZone::withCount('orders')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();

            return ApiResponse::success(ShippingZoneResource::collection($zones));
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to retrieve shipping zones');
        }
    }

    public function show(ShippingZone $shippingZone): JsonResponse
    {
        try {
            $shippingZone->loadCount('orders');

            return ApiResponse::success(new ShippingZoneResource($shippingZone));
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to retrieve shipping zone');
        }
    }

    public function store(StoreShippingZoneRequest $request): JsonResponse
    {
        try {
            // Default sort_order to max + 1 if not provided
            $data = $request->validated();
            if (! isset($data['sort_order'])) {
                $data['sort_order'] = (ShippingZone::max('sort_order') ?? 0) + 1;
            }

            $zone = ShippingZone::create($data);
            $this->bustCache();

            return ApiResponse::success(new ShippingZoneResource($zone), 'Shipping zone created', 201);
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to create shipping zone');
        }
    }

    public function update(UpdateShippingZoneRequest $request, ShippingZone $shippingZone): JsonResponse
    {
        try {
            $shippingZone->update($request->validated());
            $this->bustCache();

            return ApiResponse::success(new ShippingZoneResource($shippingZone), 'Shipping zone updated');
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to update shipping zone');
        }
    }

    public function destroy(ShippingZone $shippingZone): JsonResponse
    {
        try {
            if ($shippingZone->orders()->exists()) {
                return ApiResponse::error(
                    'Cannot delete a zone that has associated orders.',
                    null,
                    422
                );
            }

            $shippingZone->delete();
            $this->bustCache();

            return ApiResponse::success(null, 'Shipping zone deleted');
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to delete shipping zone');
        }
    }

    /**
     * Bulk-update sort_order for all zones after drag-and-drop reorder.
     * Expects: { zones: [{id, sort_order}, ...] }
     */
    public function reorder(ReorderShippingZonesRequest $request): JsonResponse
    {
        try {
            DB::transaction(function () use ($request) {
                foreach ($request->validated()['zones'] as $item) {
                    ShippingZone::where('id', $item['id'])
                        ->update(['sort_order' => $item['sort_order']]);
                }
            });

            $this->bustCache();

            return ApiResponse::success(null, 'Zones reordered successfully');
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to reorder shipping zones');
        }
    }

    private function bustCache(): void
    {
        Cache::forget(PublicShippingZoneController::CACHE_KEY);
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
