<?php

namespace App\Domains\Product\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('product.update');
    }

    public function rules(): array
    {
        $product = $this->route('product');
        $productId = $product instanceof \App\Domains\Product\Models\Product ? $product->id : $product;

        $rules = [
            'name'               => 'sometimes|string|max:255',
            'short_description'  => 'nullable|string',
            'description'        => 'nullable|string',
            'category_id'        => 'sometimes|exists:categories,id',
            'base_price'         => 'sometimes|numeric|min:0',
            'thumbnail'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'gallery'            => 'nullable|array',
            'gallery.*'          => 'image|mimes:jpg,jpeg,png,webp|max:2048',
            'gallery_remove'     => 'nullable|array',
            'gallery_remove.*'   => 'nullable|string',
            'is_active'          => 'boolean',
            'is_featured'        => 'boolean',
            'is_trending'        => 'boolean',

            'variants'                       => 'sometimes|array|min:1',
            'variants.*.title'               => 'required_with:variants|string',
            'variants.*.price'               => 'required_with:variants|numeric|min:0',
            'variants.*.stock'               => 'nullable|integer|min:0',
            'variants.*.weight_grams'        => 'nullable|integer|min:0',
            'variants.*.discount_type'       => 'nullable|in:percentage,fixed',
            'variants.*.discount_value'      => 'nullable|numeric|min:0',
            'variants.*.sale_ends_at'        => 'nullable|date',
            'variants.*.is_active'           => 'boolean',

            'landing_slug' => [
                'nullable',
                'string',
                Rule::unique('products', 'landing_slug')->ignore($productId),
            ],
            'is_landing_enabled'  => 'boolean',
            'meta_title'          => 'nullable|string|max:255',
            'meta_description'    => 'nullable|string',
            'meta_keywords'       => 'nullable|string',
        ];

        // Dynamically add SKU uniqueness and per-product ownership validation
        if ($this->has('variants')) {
            foreach ($this->input('variants') as $index => $variant) {
                $id = $variant['id'] ?? null;

                $rules["variants.{$index}.id"] = [
                    'nullable',
                    'integer',
                    Rule::exists('product_variants', 'id')->where('product_id', $productId),
                ];

                $rules["variants.{$index}.sku"] = [
                    'required_with:variants',
                    'string',
                    $id ? Rule::unique('product_variants', 'sku')->ignore($id) : Rule::unique('product_variants', 'sku'),
                ];
            }
        }

        return $rules;
    }
}
