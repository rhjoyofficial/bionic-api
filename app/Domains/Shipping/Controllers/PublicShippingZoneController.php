<?php

namespace App\Domains\Shipping\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\Shipping\Models\ShippingZone;
use App\Domains\Shipping\Resources\ShippingZoneResource;

class PublicShippingZoneController extends Controller
{
    public function index()
    {
        return ShippingZoneResource::collection(
            ShippingZone::where('is_active', true)->get()
        );
    }
}
