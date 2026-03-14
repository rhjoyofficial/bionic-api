<?php

namespace App\Domains\Order\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray($request)
    {
        return [

            'id' => $this->id,
            'order_number' => $this->order_number,

            'status' => $this->order_status,
            'payment_status' => $this->payment_status,

            'subtotal' => (float) $this->subtotal,
            'discount_total' => (float) $this->discount_total,
            'shipping_cost' => (float) $this->shipping_cost,
            'grand_total' => (float) $this->grand_total,

            'placed_at' => optional($this->placed_at)?->toDateTimeString(),

            'items' => $this->items->map(function ($i) {
                return [
                    'product_name' => $i->product_name_snapshot,
                    'variant_title' => $i->variant_title_snapshot,
                    'qty' => $i->quantity,
                    'price' => (float) $i->unit_price,
                    'total' => (float) $i->total_price,
                ];
            }),
        ];
    }
}
