<?php

namespace App\Services\Infrastructure\Drivers;

use App\Infrastructure\Courier\CourierInterface;
use Illuminate\Support\Facades\Http;

class PathaoCourier implements CourierInterface
{
  public function createShipment($order)
  {
    $response = Http::post(
      config('courier.pathao_url') . '/shipments',
      [

        'recipient_name' => $order->customer_name,

        'recipient_phone' => $order->customer_phone,

        'recipient_address' => $order->address_line,

        'amount_to_collect' => $order->grand_total

      ]
    );

    return $response->json();
  }

  public function trackShipment($trackingCode)
  {
    return Http::get(
      config('courier.pathao_url') . '/track/' . $trackingCode
    );
  }
}
