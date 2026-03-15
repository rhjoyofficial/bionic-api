<?php

namespace App\Domains\Cart\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
  public function toArray($request)
  {
    return [
      'id' => $this->id,
      'variant_id' => $this->variant_id,
      'quantity' => $this->quantity,
      'product_name_snapshot' => $this->product_name_snapshot,
      'variant_title_snapshot' => $this->variant_title_snapshot,
      'combo_name_snapshot' => $this->combo_name_snapshot,
      'unit_price' => (float) $this->unit_price_snapshot,
      'subtotal' => (float) $this->subtotal,
      'image_url' => $this->combo_id
        ? ($this->combo->image ?? null)
        : ($this->variant->product->image_url ?? null),
    ];
  }
}
