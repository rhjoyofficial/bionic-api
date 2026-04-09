<?php

namespace App\Domains\Category\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'slug'           => $this->slug,
            'description'    => $this->description,
            'image_url'      => $this->image ? asset('storage/' . $this->image) : null,
            'is_active'      => (bool) $this->is_active,
            'sort_order'     => $this->sort_order,
            'products_count' => $this->when(isset($this->products_count), $this->products_count),
            'created_at'     => $this->created_at?->toDateTimeString(),
        ];
    }
}
