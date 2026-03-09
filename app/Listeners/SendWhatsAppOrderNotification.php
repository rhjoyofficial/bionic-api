<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Services\WhatsApp\WhatsAppService;

class SendWhatsAppOrderNotification
{
  public function handle(OrderCreated $event)
  {
    $order = $event->order;

    $message = "Order {$order->order_number} confirmed.";

    app(WhatsAppService::class)->send($order->customer_phone, $message);
  }
}
