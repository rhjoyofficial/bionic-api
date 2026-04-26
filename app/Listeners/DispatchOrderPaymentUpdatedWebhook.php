<?php

namespace App\Listeners;

use App\Events\OrderPaymentUpdated;
use App\Jobs\SendWebhookJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Throwable;

class DispatchOrderPaymentUpdatedWebhook implements ShouldQueue
{
    use InteractsWithQueue;

    public bool  $afterCommit = true;
    public int   $tries       = 3;
    public array $backoff      = [30, 120, 300];

    public function handle(OrderPaymentUpdated $event): void
    {
        $order = $event->order;

        SendWebhookJob::dispatch('order.payment_updated', [
            'order_number'   => $order->order_number,
            'old_status'     => $event->oldStatus,
            'new_status'     => $event->newStatus,
            'payment_method' => $order->payment_method,
            'grand_total'    => (float) $order->grand_total,
            'customer_name'  => $order->customer_name,
            'customer_phone' => $order->customer_phone,
            'order_status'   => $order->order_status,
            'changed_at'     => now()->toISOString(),
        ]);
    }

    public function failed(Throwable $e): void
    {
        Log::error('DispatchOrderPaymentUpdatedWebhook failed: ' . $e->getMessage());
    }
}
