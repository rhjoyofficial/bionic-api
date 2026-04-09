<?php

namespace App\Domains\Product\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'               => $this->id,
            'title'            => $this->title,
            'sku'              => $this->sku,
            'price'            => (float) $this->price,
            'final_price'      => (float) $this->final_price,
            'discount_percent' => $this->discount_percent,
            'discount_type'    => $this->discount_type,
            'discount_value'   => $this->discount_value,
            'sale_ends_at'     => $this->sale_ends_at,
            'stock'            => (int) $this->stock,
            'reserved_stock'   => (int) $this->reserved_stock,
            'available_stock'  => $this->available_stock,
            'weight_grams'     => $this->weight_grams,
            'is_active'        => (bool) $this->is_active,
            'tiers'            => ProductTierResource::collection($this->whenLoaded('tierPrices')),
        ];
    }
}
