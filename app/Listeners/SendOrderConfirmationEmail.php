<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Mail\OrderConfirmationMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendOrderConfirmationEmail implements ShouldQueue
{
    use InteractsWithQueue;

    /** Dispatch only after the DB transaction that fired the event commits. */
    public bool $afterCommit = true;

    public int $tries   = 3;
    public array $backoff = [10, 30, 60];
    public int $timeout = 30;

    public function handle(OrderCreated $event): void
    {
        $order = $event->order;

        // Only send if the customer provided an email address at checkout.
        if (!$order->customer_email) {
            return;
        }

        // Ensure relationships are loaded so the template renders without N+1 queries.
        $order->loadMissing(['items', 'shippingAddress']);

        Mail::to($order->customer_email)
            ->send(new OrderConfirmationMail($order));
    }

    public function failed(Throwable $exception): void
    {
        Log::error('SendOrderConfirmationEmail: failed permanently', [
            'error' => $exception->getMessage(),
        ]);
    }
}
