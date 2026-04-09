<?php

namespace App\Domains\Product\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreComboRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('product.create');
    }

    public function rules(): array
    {
        return [
            'title'                  => 'required|string|max:200',
            'slug'                   => 'nullable|string|max:220|unique:combos,slug',
            'description'            => 'nullable|string',
            'image'                  => 'nullable|image|max:2048',
            'pricing_mode'           => 'required|in:auto,manual',
            'manual_price'           => 'nullable|numeric|min:0|required_if:pricing_mode,manual',
            'discount_type'          => 'nullable|in:percentage,fixed',
            'discount_value'         => 'nullable|numeric|min:0',
            'is_active'              => 'boolean',
            'is_featured'            => 'boolean',
            'items'                  => 'required|array|min:1',
            'items.*.variant_id'     => 'required|integer|exists:product_variants,id',
            'items.*.quantity'       => 'required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'items.required'           => 'A combo must have at least one component.',
            'items.min'                => 'A combo must have at least one component.',
            'items.*.variant_id.exists' => 'One or more selected variants do not exist.',
            'manual_price.required_if' => 'Manual price is required when pricing mode is set to manual.',
        ];
    }
}
