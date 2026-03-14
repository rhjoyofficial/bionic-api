<?php

namespace App\Infrastructure\Courier;

use App\Services\Infrastructure\Drivers\PathaoCourier;
use App\Services\Infrastructure\Drivers\RedXCourier;
use App\Services\Infrastructure\Drivers\SteadfastCourier;

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
