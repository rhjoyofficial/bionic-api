<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Jobs\SendSMSJob;

class SendOrderSMSListener
{
    public function handle(OrderCreated $event)
    {
        $order = $event->order;

        $message = "Dear {$order->customer_name}, your order {$order->order_number} has been received. Thank you for shopping with Bionic.";

        SendSMSJob::dispatch(
            $order->customer_phone,
            $message
        );
    }
}
