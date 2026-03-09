<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Services\SMS\SMSService;

class SendOrderSMSNotification
{
  public function handle(OrderCreated $event)
  {
    $order = $event->order;

    $message = "Your order {$order->order_number} has been received.";

    app(SMSService::class)
      ->send($order->customer_phone, $message);
  }
}
