<?php

namespace App\Domains\Product\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ComboResource extends JsonResource
{
    public function toArray($request): array
    {
        $itemsLoaded  = $this->relationLoaded('items');
        $itemsWithVar = $itemsLoaded && $this->items->isNotEmpty()
            && $this->items->first()->relationLoaded('variant');

        return [
            'id'            => $this->id,
            'title'         => $this->title,
            'slug'          => $this->slug,
            'description'   => $this->description,
            'image'         => $this->image ? asset('storage/' . $this->image) : null,
            'pricing_mode'  => $this->pricing_mode,
            'manual_price'  => $this->manual_price ? (float) $this->manual_price : null,
            'discount_type' => $this->discount_type,
            'discount_value'=> $this->discount_value ? (float) $this->discount_value : null,
            'is_active'     => $this->is_active,
            'is_featured'   => $this->is_featured,
            'created_at'    => $this->created_at?->toDateTimeString(),

            // Aggregates
            'items_count'   => $this->when(isset($this->items_count), $this->items_count),

            // Computed prices — only when items+variants are fully loaded
            'auto_price'      => $this->when($itemsWithVar, fn() => (float) $this->auto_price),
            'final_price'     => $this->when($itemsWithVar, fn() => (float) $this->final_price),
            'available_stock' => $this->when($itemsWithVar, fn() => (int) $this->available_stock),

            // Full items list — only when relation loaded
            'items' => $this->when($itemsLoaded, fn() => $this->items->map(fn($item) => [
                'id'       => $item->id,
                'quantity' => $item->quantity,
                'variant'  => $item->relationLoaded('variant') ? [
                    'id'              => $item->variant?->id,
                    'title'           => $item->variant?->title,
                    'sku'             => $item->variant?->sku,
                    'price'           => $item->variant ? (float) $item->variant->price : null,
                    'final_price'     => $item->variant ? (float) $item->variant->final_price : null,
                    'available_stock' => $item->variant?->available_stock,
                    'product'         => $item->variant?->relationLoaded('product') ? [
                        'id'        => $item->variant->product?->id,
                        'name'      => $item->variant->product?->name,
                        'thumbnail' => $item->variant->product?->thumbnail
                            ? asset('storage/' . $item->variant->product->thumbnail)
                            : null,
                    ] : null,
                ] : null,
            ])),
        ];
    }
}
