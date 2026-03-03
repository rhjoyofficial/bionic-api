<?php

namespace App\Domains\Shipping\Services;

use App\Domains\Shipping\Models\ShippingZone;

class ShippingCalculator
{
    public function calculate(ShippingZone $zone, float $orderAmount): float
    {
        if (
            $zone->free_shipping_threshold &&
            $orderAmount >= $zone->free_shipping_threshold
        ) {
            return 0;
        }

        return $zone->base_charge;
    }
}
