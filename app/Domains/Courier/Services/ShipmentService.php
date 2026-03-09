<?php

namespace App\Domains\Courier\Services;

use App\Services\Courier\CourierService;
use App\Domains\Courier\Models\CourierShipment;

class ShipmentService
{
  public function create($order)
  {
    $driver = app(CourierService::class)->driver();

    $response = $driver->createShipment($order);

    return CourierShipment::create([

      'order_id' => $order->id,

      'courier' => config('courier.default'),

      'tracking_code' => $response['tracking_code'] ?? null,

      'status' => 'created'

    ]);
  }
}
