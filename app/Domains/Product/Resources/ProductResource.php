<?php

namespace App\Domains\Product\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'                  => $this->id,
            'name'                => $this->name,
            'slug'                => $this->slug,
            'short_description'   => $this->short_description,
            'description'         => $this->description,
            'base_price'          => (float) $this->base_price,
            'thumbnail'           => $this->thumbnail,
            'image_url'           => $this->image_url,
            'gallery'             => $this->gallery ?? [],
            'is_active'           => (bool) $this->is_active,
            'is_featured'         => (bool) $this->is_featured,
            'is_trending'         => (bool) $this->is_trending,
            'meta_title'          => $this->meta_title,
            'meta_description'    => $this->meta_description,
            'meta_keywords'       => $this->meta_keywords,
            'landing_slug'        => $this->landing_slug,
            'is_landing_enabled'  => (bool) $this->is_landing_enabled,
            'category'            => [
                'id'   => $this->category?->id,
                'name' => $this->category?->name,
                'slug' => $this->category?->slug,
            ],
            'variants' => $this->relationLoaded('allVariants')
                ? ProductVariantResource::collection($this->allVariants)
                : ProductVariantResource::collection($this->whenLoaded('variants')),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
