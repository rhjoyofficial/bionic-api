<?php

namespace App\Services\Courier;

interface CourierInterface
{
  public function createShipment($order);

  public function trackShipment($trackingCode);
}
