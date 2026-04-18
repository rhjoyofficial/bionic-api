<?php

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Mail\OrderStatusMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendOrderStatusEmail implements ShouldQueue
{
    use InteractsWithQueue;

    /** Queue only after status transaction commits. */
    public bool $afterCommit = true;
    public int $tries = 3;
    public array $backoff = [10, 30, 60];
    public int $timeout = 30;

    public function handle(OrderStatusChanged $event): void
    {
        $order = $event->order;

        if (!$order->customer_email) {
            Log::info('SendOrderStatusEmail skipped: missing customer email', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'new_status' => $event->newStatus,
            ]);
            return;
        }

        // We do not email when status is 'pending' because OrderConfirmationMail covers it
        if ($event->newStatus === 'pending') {
            return;
        }

        try {
            $fromAddress = env('NOREPLY_MAIL_FROM_ADDRESS', 'no-reply@bionic.garden');
            $fromName    = env('NOREPLY_MAIL_FROM_NAME', config('app.name') . ' Orders');

            Mail::mailer('noreply')
                ->to($order->customer_email)
                ->send(
                    (new OrderStatusMail($order, $event->oldStatus, $event->newStatus))
                        ->from($fromAddress, $fromName)
                );
        } catch (Throwable $e) {
            Log::error('SendOrderStatusEmail failed', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'customer_email' => $order->customer_email,
                'old_status' => $event->oldStatus,
                'new_status' => $event->newStatus,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
