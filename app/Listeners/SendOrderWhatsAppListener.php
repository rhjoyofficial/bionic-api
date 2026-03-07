<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Jobs\SendWhatsAppJob;

class SendOrderWhatsAppListener
{
    public function handle(OrderCreated $event)
    {
        $order = $event->order;

        $message =
            "Hello {$order->customer_name}, your order {$order->order_number} has been received. Thank you for shopping with Bionic.";

        SendWhatsAppJob::dispatch(
            $order->customer_phone,
            $message
        );
    }
}
