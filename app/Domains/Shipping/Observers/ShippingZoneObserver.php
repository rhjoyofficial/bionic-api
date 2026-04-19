<?php

namespace App\Domains\Shipping\Observers;

use App\Domains\Shipping\Models\ShippingZone;
use Illuminate\Support\Facades\Cache;

class ShippingZoneObserver
{
    private function clearCache(): void
    {
        Cache::forget('shipping:zones:active');
    }

    public function created(ShippingZone $shippingZone): void { $this->clearCache(); }
    public function updated(ShippingZone $shippingZone): void { $this->clearCache(); }
    public function deleted(ShippingZone $shippingZone): void { $this->clearCache(); }
}
