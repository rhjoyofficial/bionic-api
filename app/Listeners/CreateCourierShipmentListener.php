<?php

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Domains\Courier\Services\ShipmentService;

class CreateCourierShipmentListener
{
    public function __construct(
        private ShipmentService $shipmentService
    ) {}

    public function handle(OrderStatusChanged $event): void
    {
        if ($event->newStatus !== 'confirmed') {
            return;
        }
        return;
    }
}
