<?php

namespace App\Domains\Order\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // guest allowed
    }

    public function rules(): array
    {
        return [
            'customer_name' => 'required|string',
            'customer_phone' => 'required|string',
            'customer_email' => 'nullable|email',
            'address_line' => 'required|string',
            'city' => 'required|string',
            'zone_id' => 'required|exists:shipping_zones,id',

            'items' => 'required|array|min:1',
            'items.*.variant_id' => 'required|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',

            'coupon_code' => 'nullable|string'
        ];
    }
}
