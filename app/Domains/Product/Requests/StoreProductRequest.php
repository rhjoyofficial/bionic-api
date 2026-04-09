<?php

namespace App\Domains\Product\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('product.create');
    }

    public function rules(): array
    {
        return [
            'name'               => 'required|string|max:255',
            'short_description'  => 'nullable|string',
            'description'        => 'nullable|string',
            'category_id'        => 'required|exists:categories,id',
            'base_price'         => 'required|numeric|min:0',
            'thumbnail'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'gallery'            => 'nullable|array',
            'gallery.*'          => 'image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_active'          => 'boolean',
            'is_featured'        => 'boolean',
            'is_trending'        => 'boolean',

            'variants'                       => 'required|array|min:1',
            'variants.*.title'               => 'required|string',
            'variants.*.sku'                 => 'required|string|unique:product_variants,sku',
            'variants.*.price'               => 'required|numeric|min:0',
            'variants.*.stock'               => 'nullable|integer|min:0',
            'variants.*.weight_grams'        => 'nullable|integer|min:0',
            'variants.*.discount_type'       => 'nullable|in:percentage,fixed',
            'variants.*.discount_value'      => 'nullable|numeric|min:0',
            'variants.*.sale_ends_at'        => 'nullable|date',
            'variants.*.is_active'           => 'boolean',

            'landing_slug'        => 'nullable|string|unique:products,landing_slug',
            'is_landing_enabled'  => 'boolean',
            'meta_title'          => 'nullable|string|max:255',
            'meta_description'    => 'nullable|string',
            'meta_keywords'       => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'variants.required'       => 'At least one product variant is required.',
            'variants.*.sku.unique'   => 'The SKU :input is already taken.',
            'gallery.*.image'         => 'Each gallery item must be an image.',
        ];
    }
}
