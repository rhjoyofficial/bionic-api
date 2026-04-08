<?php

namespace App\Domains\Shipping\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\Shipping\Models\ShippingZone;
use App\Domains\Shipping\Resources\ShippingZoneResource;
use App\Helpers\ApiResponse;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Facades\Cache;

class PublicShippingZoneController extends Controller
{
    /** Cache key — referenced by AdminShippingZoneController for invalidation. */
    public const CACHE_KEY = 'shipping_zones_active';

    /**
     * Display a listing of active shipping zones ordered by sort_order.
     * Result is cached so the DB is not hit on every checkout page load.
     */
    public function index()
    {
        try {
            $zones = Cache::remember(
                'shipping_zones_active',
                300,
                fn() =>
                ShippingZone::where('is_active', true)->orderBy('sort_order')->get()
            );

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
