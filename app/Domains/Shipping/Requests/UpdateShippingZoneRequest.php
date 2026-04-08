<?php

namespace App\Domains\Shipping\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShippingZoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('shipping.manage');
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:150',
            'base_charge' => 'sometimes|numeric|min:0',
            'free_shipping_threshold' => 'nullable|numeric|min:0',
            'estimated_days' => 'nullable|integer|min:1',
            'is_active'  => 'boolean',
            'sort_order' => 'nullable|integer|min:0|max:9999',
        ];
    }
}
