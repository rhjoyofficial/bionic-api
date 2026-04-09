<?php

namespace App\Domains\Product\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateComboRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('product.update');
    }

    public function rules(): array
    {
        $comboId = $this->route('combo')?->id;

        return [
            'title'                  => 'sometimes|string|max:200',
            'slug'                   => "nullable|string|max:220|unique:combos,slug,{$comboId}",
            'description'            => 'nullable|string',
            'image'                  => 'nullable|image|max:2048',
            'pricing_mode'           => 'sometimes|in:auto,manual',
            'manual_price'           => 'nullable|numeric|min:0',
            'discount_type'          => 'nullable|in:percentage,fixed',
            'discount_value'         => 'nullable|numeric|min:0',
            'is_active'              => 'boolean',
            'is_featured'            => 'boolean',
            'items'                  => 'sometimes|array|min:1',
            'items.*.variant_id'     => 'required_with:items|integer|exists:product_variants,id',
            'items.*.quantity'       => 'required_with:items|integer|min:1',
        ];
    }
}
