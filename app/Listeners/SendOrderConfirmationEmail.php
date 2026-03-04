<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendOrderConfirmationEmail implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(OrderCreated $event): void
    {
        $order = $event->order;

        // Example placeholder email logic
        if ($order->customer_email) {

            Mail::raw(
                "Your order {$order->order_number} has been received.",
                function ($message) use ($order) {
                    $message->to($order->customer_email)
                        ->subject('Order Confirmation');
                }
            );
        }
    }
}
