<?php

namespace App\Domains\Order\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'order_id'     => $this->order_id,
            'order_number' => $this->whenLoaded('order', fn() => $this->order->order_number),
            'type'         => $this->type,
            'amount'       => (float) $this->amount,
            'description'  => $this->description,
            'metadata'     => $this->metadata,
            'created_at'   => $this->created_at?->toISOString(),
        ];
    }
}
