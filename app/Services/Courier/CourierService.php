<?php

namespace App\Services\Courier;

class CourierService
{
  public function driver()
  {
    $courier = config('courier.default');

    return match ($courier) {

      'pathao' => app(PathaoCourier::class),

      'redx' => app(RedXCourier::class),

      'steadfast' => app(SteadfastCourier::class),

      default => throw new \Exception('Courier not supported')
    };
  }
}
