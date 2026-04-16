<?php

namespace App\Domains\Landing\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LandingPageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'slug'             => $this->slug,
            'type'             => $this->type,
            'title'            => $this->title,
            'hero_image'       => $this->hero_image,
            'blade_template'   => $this->blade_template,
            'content'          => $this->content,
            'meta_title'       => $this->meta_title,
            'meta_description' => $this->meta_description,
            'pixel_event_name' => $this->pixel_event_name,
            'config'           => $this->config,
            'is_active'        => $this->is_active,
            'created_at'       => $this->created_at?->toDateTimeString(),
            'updated_at'       => $this->updated_at?->toDateTimeString(),

            // Relationships (only loaded when eager-loaded)
            'product'          => $this->whenLoaded('product', fn() => [
                'id'        => $this->product->id,
                'name'      => $this->product->name,
                'thumbnail' => $this->product->thumbnail,
            ]),
            'combo'            => $this->whenLoaded('combo', fn() => [
                'id'    => $this->combo->id,
                'name'  => $this->combo->name,
                'image' => $this->combo->image,
            ]),
            'items'            => $this->whenLoaded('items'),
        ];
    }
}
