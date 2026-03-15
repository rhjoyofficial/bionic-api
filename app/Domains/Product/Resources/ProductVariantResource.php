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
      'final_price'      => (float) $this->final_price, // From your model attribute
      'discount_percent' => $this->discount_percent,   // From your model attribute
      'available_stock'  => $this->available_stock,   // From your model attribute
      'tiers'            => ProductTierResource::collection($this->whenLoaded('tierPrices')),
    ];
  }
}
