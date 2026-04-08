<?php

namespace App\Domains\Product\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'slug'        => $this->slug,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'image_url'   => $this->image_url, // Using your custom model getter
            'gallery'     => $this->gallery ?? [],
            'base_price'  => (float) $this->base_price,
            'is_trending' => (bool) $this->is_trending,
            'category'    => [
                'id'   => $this->category?->id,
                'name' => $this->category?->name,
            ],
            'variants'    => ProductVariantResource::collection($this->whenLoaded('variants')),
        ];
    }
}
