<?php

namespace App\Domains\Product\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductTierResource extends JsonResource
{
  public function toArray($request)
  {
    return [
      'qty'   => $this->min_quantity,
      'type'  => $this->discount_type,
      'value' => $this->discount_value
    ];
  }
}
