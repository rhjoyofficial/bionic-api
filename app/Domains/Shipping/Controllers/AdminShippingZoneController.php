<?php

namespace App\Domains\Shipping\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\Shipping\Models\ShippingZone;
use App\Domains\Shipping\Requests\StoreShippingZoneRequest;
use App\Domains\Shipping\Requests\UpdateShippingZoneRequest;
use App\Domains\Shipping\Resources\ShippingZoneResource;
use Illuminate\Support\Facades\Log;
use Exception;

class AdminShippingZoneController extends Controller
{
    public function index()
    {
        try {
            $zones = ShippingZone::latest()->paginate(10);
            return ShippingZoneResource::collection($zones);
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to retrieve shipping zones');
        }
    }

    public function store(StoreShippingZoneRequest $request)
    {
        try {
            $zone = ShippingZone::create($request->validated());

            return (new ShippingZoneResource($zone))
                ->additional(['success' => true, 'message' => 'Shipping zone created successfully'])
                ->response()
                ->setStatusCode(201);
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to create shipping zone');
        }
    }

    public function update(UpdateShippingZoneRequest $request, ShippingZone $shippingZone)
    {
        try {
            $shippingZone->update($request->validated());

            return (new ShippingZoneResource($shippingZone))
                ->additional(['success' => true, 'message' => 'Shipping zone updated successfully']);
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to update shipping zone');
        }
    }

    public function destroy(ShippingZone $shippingZone)
    {
        try {
            $shippingZone->delete();

            return response()->json([
                'success' => true,
                'message' => 'Shipping zone deleted successfully'
            ]);
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to delete shipping zone');
        }
    }

    /**
     * Common error response handler
     */
    private function handleError(Exception $e, string $msg)
    {
        Log::error($msg . ': ' . $e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);

        return response()->json([
            'success' => false,
            'message' => $msg,
            'error'   => config('app.debug') ? $e->getMessage() : 'Server Error'
        ], 500);
    }
}
