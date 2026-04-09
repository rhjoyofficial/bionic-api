<?php

namespace App\Domains\Shipping\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReorderShippingZonesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('shipping.update');
    }

    public function rules(): array
    {
        return [
            'zones'              => 'required|array|min:1',
            'zones.*.id'         => 'required|integer|exists:shipping_zones,id',
            'zones.*.sort_order' => 'required|integer|min:0',
        ];
    }
}
