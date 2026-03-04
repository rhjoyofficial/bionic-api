<?php

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendOrderStatusEmail implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(OrderStatusChanged $event): void
    {
        $order = $event->order;

        if ($order->customer_email) {

            Mail::raw(
                "Your order status is now {$event->newStatus}",
                function ($message) use ($order) {
                    $message->to($order->customer_email)
                        ->subject('Order Status Update');
                }
            );
        }
    }
}
