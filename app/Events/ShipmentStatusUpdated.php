<?php

namespace App\Events;

use App\Domains\Courier\Models\CourierShipment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ShipmentStatusUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly CourierShipment $shipment,
        public readonly string $oldStatus,
        public readonly string $newStatus,
    ) {}
}
