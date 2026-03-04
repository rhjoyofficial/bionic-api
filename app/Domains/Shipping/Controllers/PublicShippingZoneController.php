<?php

namespace App\Domains\Shipping\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\Shipping\Models\ShippingZone;
use App\Domains\Shipping\Resources\ShippingZoneResource;
use App\Support\ApiResponse;
use Illuminate\Support\Facades\Log;
use Exception;

class PublicShippingZoneController extends Controller
{
    /**
     * Display a listing of active shipping zones.
     */
    public function index()
    {
        try {
            $zones = ShippingZone::where('is_active', true)
                ->latest()
                ->get();

            return ApiResponse::success(
                ShippingZoneResource::collection($zones),
                'Shipping zones retrieved successfully'
            );
        } catch (Exception $e) {
            Log::error('Public Shipping Zone Index Error: ' . $e->getMessage());

            return ApiResponse::error(
                'Unable to load shipping zones at this time.',
                config('app.debug') ? $e->getMessage() : null,
                500
            );
        }
    }
}
