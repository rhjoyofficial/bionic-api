<?php

namespace App\Domains\Customer\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AdminCustomerResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'email'            => $this->email,
            'phone'            => $this->phone,
            'is_guest'         => $this->is_guest,
            'is_active'        => $this->is_active,
            'referral_code'    => $this->referral_code,
            'last_login_at'    => $this->last_login_at?->toDateTimeString(),
            'created_at'       => $this->created_at?->toDateTimeString(),

            // Aggregates (available when loaded via withCount/withSum)
            'orders_count'     => $this->when(isset($this->orders_count), $this->orders_count),
            'orders_sum_grand_total' => $this->when(
                isset($this->orders_sum_grand_total),
                fn() => (float) $this->orders_sum_grand_total
            ),

            // Recent orders (available when relation is loaded)
            'recent_orders'    => $this->when(
                $this->relationLoaded('orders'),
                fn() => $this->orders->map(fn($order) => [
                    'id'           => $order->id,
                    'order_number' => $order->order_number,
                    'order_status' => $order->order_status,
                    'payment_status' => $order->payment_status,
                    'grand_total'  => (float) $order->grand_total,
                    'items_count'  => $order->items->count(),
                    'placed_at'    => $order->placed_at?->toDateTimeString(),
                ])
            ),
        ];
    }
}
