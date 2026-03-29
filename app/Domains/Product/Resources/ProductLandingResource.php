<?php

namespace App\Domains\Product\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductLandingResource extends JsonResource
{
    public function toArray($request)
    {
        return [

            'product' => [
                'id' => $this->id,
                'name' => $this->name,
                'slug' => $this->slug,
                'price' => $this->base_price,
                'thumbnail' => $this->thumbnail
            ],

            'seo' => [

                'title' => $this->meta_title ?? $this->name,

                'description' => $this->meta_description,

                'keywords' => $this->meta_keywords,

                'thumbnail' => $this->thumbnail,

            ],

            'variants' => $this->variants->map(function ($v) {
                return [
                    'id' => $v->id,
                    'title' => $v->title,
                    'sku' => $v->sku,
                    'price' => (float) $v->price,
                    'final_price' => (float) $v->final_price,
                    'discount_percent' => $v->discount_percent,
                    'available_stock' => $v->available_stock,
                ];
            }),

            'description' => $this->description
        ];
    }
}
