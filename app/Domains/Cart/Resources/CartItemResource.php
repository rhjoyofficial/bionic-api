<?php

namespace App\Domains\Cart\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
  public function toArray($request)
  {
    $originalUnitPrice = null;
    $tierSaving        = null;
    $tiers             = [];

    if ($this->variant_id && $this->variant) {
      $originalUnitPrice = (float) $this->variant->final_price;

      $diff = $originalUnitPrice - (float) $this->unit_price_snapshot;
      if ($diff > 0.001) {
        $tierSaving = round($diff, 2);
      }

      // Always expose all tiers so the frontend can show "add N more to unlock X% off"
      $tiers = $this->variant->tierPrices
        ->sortBy('min_quantity')
        ->map(fn($t) => [
          'qty'   => (int)   $t->min_quantity,
          'type'  =>         $t->discount_type,
          'value' => (float) $t->discount_value,
        ])
        ->values()
        ->toArray();
    }

    return [
      'id'                     => $this->id,
      'variant_id'             => $this->variant_id,
      'quantity'               => $this->quantity,
      'product_name_snapshot'  => $this->product_name_snapshot,
      'variant_title_snapshot' => $this->variant_title_snapshot,
      'combo_name_snapshot'    => $this->combo_name_snapshot,
      'unit_price'             => (float) $this->unit_price_snapshot,
      'original_unit_price'    => $originalUnitPrice,
      'tier_saving'            => $tierSaving,   // non-null = tier currently active
      'tiers'                  => $tiers,        // all available tiers for this variant
      'subtotal'               => (float) $this->subtotal,
      'image_url'              => $this->combo_id
        ? ($this->combo->image ?? null)
        : ($this->variant->product->image_url ?? null),
    ];
  }
}
