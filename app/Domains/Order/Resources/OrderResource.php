<?php

namespace App\Domains\Order\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'             => $this->id,
            'order_number'   => $this->order_number,
            'customer_name'  => $this->customer_name,
            'customer_phone' => $this->customer_phone,
            'customer_email' => $this->customer_email,
            'notes'          => $this->notes,

            'order_status'   => $this->order_status,
            'payment_status' => $this->payment_status,
            'payment_method' => $this->payment_method,

            'subtotal'        => (float) $this->subtotal,
            'discount_total'  => (float) $this->discount_total,
            'shipping_cost'   => (float) $this->shipping_cost,
            'grand_total'     => (float) $this->grand_total,
            'coupon_discount' => (float) $this->coupon_discount,
            'coupon_code'     => $this->coupon_code_snapshot,

            'is_editable' => $this->isEditable(),

            'items_count' => $this->whenCounted('items'),

            'placed_at'     => $this->placed_at?->toDateTimeString(),
            'confirmed_at'  => $this->confirmed_at?->toDateTimeString(),
            'processing_at' => $this->processing_at?->toDateTimeString(),
            'shipped_at'    => $this->shipped_at?->toDateTimeString(),
            'delivered_at'  => $this->delivered_at?->toDateTimeString(),
            'cancelled_at'  => $this->cancelled_at?->toDateTimeString(),

            'zone' => $this->whenLoaded('zone', fn() => [
                'id'   => $this->zone->id,
                'name' => $this->zone->name,
            ]),

            'user' => $this->whenLoaded('user', fn() => $this->user ? [
                'id'    => $this->user->id,
                'name'  => $this->user->name,
                'phone' => $this->user->phone,
                'email' => $this->user->email,
            ] : null),

            'shipping_address' => $this->whenLoaded('shippingAddress', fn() => $this->shippingAddress ? [
                'address_line' => $this->shippingAddress->address_line,
                'area'         => $this->shippingAddress->area,
                'city'         => $this->shippingAddress->city,
                'postal_code'  => $this->shippingAddress->postal_code,
            ] : null),

            'items' => $this->whenLoaded('items', fn() => $this->items->map(fn($i) => [
                'id'              => $i->id,
                'variant_id'      => $i->variant_id,
                'combo_id'        => $i->combo_id,
                'product_name'    => $i->product_name_snapshot,
                'variant_title'   => $i->variant_title_snapshot,
                'sku'             => $i->sku_snapshot,
                'qty'             => $i->quantity,
                'unit_price'      => (float) $i->unit_price,
                'original_price'  => (float) $i->original_unit_price,
                'total'           => (float) $i->total_price,
            ])->values()),

            'admin_notes' => $this->whenLoaded('adminNotes', fn() => $this->adminNotes->map(fn($n) => [
                'id'         => $n->id,
                'body'       => $n->body,
                'admin_name' => $n->admin?->name ?? 'System',
                'created_at' => $n->created_at?->toDateTimeString(),
            ])->values()),

            'shipments' => $this->whenLoaded('shipments', fn() => $this->shipments->map(fn($s) => [
                'id'                     => $s->id,
                'courier'                => $s->courier,
                'courier_label'          => match ($s->courier) {
                    'pathao'    => 'Pathao',
                    'steadfast' => 'Steadfast',
                    'carrybee'  => 'CarryBee',
                    default     => ucfirst($s->courier),
                },
                'tracking_code'          => $s->tracking_code,
                'consignment_id'         => $s->consignment_id,
                'status'                 => $s->status,
                'status_label'           => $s->status_label,
                'delivery_fee'           => $s->delivery_fee ? (float) $s->delivery_fee : null,
                'cod_amount'             => $s->cod_amount ? (float) $s->cod_amount : null,
                'courier_status_message' => $s->courier_status_message,
                'is_cancellable'         => $s->isCancellable(),
                'is_terminal'            => $s->isTerminal(),
                'created_by_name'        => $s->creator?->name ?? null,
                'picked_at'              => $s->picked_at?->toDateTimeString(),
                'delivered_at'           => $s->delivered_at?->toDateTimeString(),
                'status_synced_at'       => $s->status_synced_at?->toDateTimeString(),
                'created_at'             => $s->created_at?->toDateTimeString(),
            ])->values()),

            'timeline' => $this->buildTimeline(),
        ];
    }

    private function buildTimeline(): array
    {
        $events = [];

        $map = [
            'placed_at'     => ['Order Placed',       'pending'],
            'confirmed_at'  => ['Order Confirmed',    'confirmed'],
            'processing_at' => ['Processing Started', 'processing'],
            'shipped_at'    => ['Order Shipped',      'shipped'],
            'delivered_at'  => ['Order Delivered',    'delivered'],
            'cancelled_at'  => ['Order Cancelled',    'cancelled'],
        ];

        foreach ($map as $field => [$label, $status]) {
            if ($this->$field) {
                $events[] = [
                    'status' => $status,
                    'label'  => $label,
                    'at'     => $this->$field->toDateTimeString(),
                ];
            }
        }

        usort($events, fn($a, $b) => $a['at'] <=> $b['at']);

        return $events;
    }
}
