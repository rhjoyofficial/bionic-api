<?php

namespace App\Domains\Shipping\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShippingZoneResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'base_charge' => $this->base_charge,
            'free_shipping_threshold' => $this->free_shipping_threshold,
            'estimated_days' => $this->estimated_days,
            'is_active' => $this->is_active,
        ];
    }
}
