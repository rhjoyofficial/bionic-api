<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Jobs\SendSMSJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Notifies the shop owner / admin team whenever a new order is placed.
 *
 * Channels used based on what is configured in .env:
 *   - SMS   → ADMIN_PHONE  (via SendSMSJob)
 *   - Email → ADMIN_EMAIL
 *
 * Both channels are skipped gracefully when the env var is empty,
 * so no errors occur in fresh or test environments.
 *
 * Add ADMIN_PHONE and ADMIN_EMAIL to your .env to enable notifications.
 */
class NotifyAdminOnNewOrder implements ShouldQueue
{
    use InteractsWithQueue;

    /** Dispatch only after the DB transaction that fired the event commits. */
    public bool $afterCommit = true;

    public int $tries    = 3;
    public array $backoff = [10, 30, 60];
    public int $timeout  = 20;

    public function handle(OrderCreated $event): void
    {
        $order      = $event->order;
        $adminPhone = config('bionic.admin_phone');
        $adminEmail = config('bionic.admin_email');

        $smsBody = sprintf(
            '[%s] New order #%s | %s | ৳%s | %s',
            config('app.name'),
            $order->order_number,
            $order->customer_phone,
            number_format($order->grand_total, 2),
            strtoupper($order->payment_method),
        );

        // ── SMS ───────────────────────────────────────────────────────────────
        if ($adminPhone) {
            SendSMSJob::dispatch($adminPhone, $smsBody);
        }

        // ── Email ─────────────────────────────────────────────────────────────
        if ($adminEmail) {
            $emailBody = implode("\n", [
                'New order received!',
                '',
                'Order:    #' . $order->order_number,
                'Customer: ' . $order->customer_name . ' (' . $order->customer_phone . ')',
                'Total:    ৳' . number_format($order->grand_total, 2),
                'Payment:  ' . strtoupper($order->payment_method),
                '',
                'View order: ' . route('order.success', ['order' => $order->order_number]),
            ]);

            Mail::raw(
                $emailBody,
                fn($msg) => $msg
                    ->to($adminEmail)
                    ->subject('[' . config('app.name') . '] New Order #' . $order->order_number),
            );
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error('NotifyAdminOnNewOrder: failed permanently', [
            'error' => $exception->getMessage(),
        ]);
    }
}
