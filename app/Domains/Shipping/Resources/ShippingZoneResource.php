<?php

namespace App\Domains\Shipping\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShippingZoneResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                      => $this->id,
            'name'                    => $this->name,
            'base_charge'             => (float) $this->base_charge,
            'free_shipping_threshold' => $this->free_shipping_threshold
                ? (float) $this->free_shipping_threshold
                : null,
            'estimated_days'          => $this->estimated_days,
            'is_active'               => $this->is_active,
            'sort_order'              => $this->sort_order,
            'created_at'              => $this->created_at?->toDateTimeString(),

            // Loaded via withCount('orders')
            'orders_count'            => $this->when(
                isset($this->orders_count),
                $this->orders_count
            ),
        ];
    }
}
