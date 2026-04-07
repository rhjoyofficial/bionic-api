<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Jobs\SendSMSJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendOrderSMSListener implements ShouldQueue
{
    use InteractsWithQueue;
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
