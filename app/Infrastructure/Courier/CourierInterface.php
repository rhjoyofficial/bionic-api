<?php

namespace App\Infrastructure\Courier;

interface CourierInterface
{
  public function createShipment($order);

  public function trackShipment($trackingCode);
}
