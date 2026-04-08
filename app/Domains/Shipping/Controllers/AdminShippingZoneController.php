<?php

namespace App\Domains\Shipping\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\Shipping\Models\ShippingZone;
use App\Domains\Shipping\Requests\StoreShippingZoneRequest;
use App\Domains\Shipping\Requests\UpdateShippingZoneRequest;
use App\Domains\Shipping\Resources\ShippingZoneResource;
use App\Helpers\ApiResponse; // Import your new helper
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

class AdminShippingZoneController extends Controller
{
    public function index()
    {
        try {
            $zones = ShippingZone::latest()->paginate(10);

            // Standardizes pagination meta data automatically
            return ApiResponse::paginated(ShippingZoneResource::collection($zones));
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to retrieve shipping zones');
        }
    }

    public function store(StoreShippingZoneRequest $request)
    {
        try {
            $zone = ShippingZone::create($request->validated());
            Cache::forget(PublicShippingZoneController::CACHE_KEY);

            return ApiResponse::success(
                new ShippingZoneResource($zone),
                'Shipping zone created successfully',
                201
            );
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to create shipping zone');
        }
    }

    public function update(UpdateShippingZoneRequest $request, ShippingZone $shippingZone)
    {
        try {
            $shippingZone->update($request->validated());
            Cache::forget(PublicShippingZoneController::CACHE_KEY);

            return ApiResponse::success(
                new ShippingZoneResource($shippingZone),
                'Shipping zone updated successfully'
            );
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to update shipping zone');
        }
    }

    public function destroy(ShippingZone $shippingZone)
    {
        try {
            $shippingZone->delete();
            Cache::forget(PublicShippingZoneController::CACHE_KEY);

            return ApiResponse::success(null, 'Shipping zone deleted successfully');
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to delete shipping zone');
        }
    }

    /**
     * Unified error handler using ApiResponse
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
