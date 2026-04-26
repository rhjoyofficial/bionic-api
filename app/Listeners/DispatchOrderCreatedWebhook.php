<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Jobs\SendWebhookJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Throwable;

class DispatchOrderCreatedWebhook implements ShouldQueue
{
    use InteractsWithQueue;

    public bool  $afterCommit = true;
    public int   $tries       = 3;
    public array $backoff      = [30, 120, 300];

    public function handle(OrderCreated $event): void
    {
        $order = $event->order;
        $order->loadMissing(['items', 'zone', 'shippingAddress']);

        SendWebhookJob::dispatch('order.created', [
            'order_number'    => $order->order_number,
            'customer_name'   => $order->customer_name,
            'customer_phone'  => $order->customer_phone,
            'customer_email'  => $order->customer_email,
            'grand_total'     => (float) $order->grand_total,
            'subtotal'        => (float) $order->subtotal,
            'discount_total'  => (float) $order->discount_total,
            'shipping_cost'   => (float) $order->shipping_cost,
            'coupon_code'     => $order->coupon_code_snapshot,
            'payment_method'  => $order->payment_method,
            'payment_status'  => $order->payment_status,
            'order_status'    => $order->order_status,
            'shipping_zone'   => $order->zone?->name,
            'address'         => $order->shippingAddress?->address_line,
            'items_count'     => $order->items->count(),
            'items'           => $order->items->map(fn($i) => [
                'sku'      => $i->sku_snapshot,
                'name'     => $i->name_snapshot,
                'quantity' => $i->quantity,
                'price'    => (float) $i->unit_price,
            ])->toArray(),
            'placed_at'       => $order->placed_at?->toISOString(),
        ]);
    }

    public function failed(Throwable $e): void
    {
        Log::error('DispatchOrderCreatedWebhook failed: ' . $e->getMessage());
    }
}
