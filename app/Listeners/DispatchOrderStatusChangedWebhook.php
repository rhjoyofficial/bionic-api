<?php

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Jobs\SendWebhookJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Throwable;

class DispatchOrderStatusChangedWebhook implements ShouldQueue
{
    use InteractsWithQueue;

    public bool  $afterCommit = true;
    public int   $tries       = 3;
    public array $backoff      = [30, 120, 300];

    public function handle(OrderStatusChanged $event): void
    {
        $order = $event->order;

        SendWebhookJob::dispatch('order.status_changed', [
            'order_number'   => $order->order_number,
            'old_status'     => $event->oldStatus,
            'new_status'     => $event->newStatus,
            'customer_name'  => $order->customer_name,
            'customer_phone' => $order->customer_phone,
            'grand_total'    => (float) $order->grand_total,
            'payment_method' => $order->payment_method,
            'payment_status' => $order->payment_status,
            'changed_at'     => now()->toISOString(),
        ]);
    }

    public function failed(Throwable $e): void
    {
        Log::error('DispatchOrderStatusChangedWebhook failed: ' . $e->getMessage());
    }
}
