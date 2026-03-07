<?php

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Domains\Courier\Services\ShipmentService;

class CreateCourierShipmentListener
{
    public function handle(OrderStatusChanged $event)
    {
        if ($event->newStatus !== 'confirmed') {
            return;
        }

        app(ShipmentService::class)
            ->create($event->order);
    }
}
