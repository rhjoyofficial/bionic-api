<?php

namespace App\Domains\Product\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('product.update');
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'short_description' => 'nullable|string',
            'description' => 'nullable|string',
            'category_id' => 'sometimes|exists:categories,id',
            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'gallery' => 'nullable|array',
            'gallery.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
            'base_price' => 'sometimes|numeric|min:0',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',

            'variants' => 'required|array|min:1',
            'variants.*.title' => 'required|string',
            'variants.*.sku' => 'required|string|unique:product_variants,sku',
            'variants.*.price' => 'required|numeric|min:0',
            'variants.*.stock' => 'nullable|integer|min:0',
            'variants.*.weight_grams' => 'nullable|integer|min:0',

            'landing_slug' => 'nullable|string|unique:products,landing_slug',
            'is_landing_enabled' => 'boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
        ];
    }
}
