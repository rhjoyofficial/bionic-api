<?php

namespace App\Domains\Product\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'image_url' => $this->image ? asset('storage/' . $this->image) : null,
            'base_price' => (float) $this->base_price,
            'is_active' => (bool) $this->is_active,
            'variants' => $this->variants,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
