<?php

namespace App\Domains\Order\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('order.update');
    }

    public function rules(): array
    {
        return [
            // Admin-creatable types: exclude system-generated coupon/shipping
            'type'        => 'required|in:charge,refund,discount,commission',
            'amount'      => 'required|numeric|min:0.01|max:9999999',
            'description' => 'required|string|max:255',
            'metadata'    => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'type.in' => 'Type must be one of: charge, refund, discount, commission.',
        ];
    }
}
