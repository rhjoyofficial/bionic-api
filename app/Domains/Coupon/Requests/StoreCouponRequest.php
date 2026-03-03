<?php

namespace App\Domains\Coupon\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('coupon.manage');
    }

    public function rules(): array
    {
        return [
            'code'              => 'required|string|unique:coupons,code|max:50',
            'type'              => 'required|in:fixed,percentage',
            'value'             => 'required|numeric|min:0',
            'min_order_amount'  => 'nullable|numeric|min:0',
            'max_uses'          => 'nullable|integer|min:1',
            'starts_at'         => 'nullable|date|after_or_equal:today',
            'expires_at'        => 'nullable|date|after:starts_at',
            'is_active'         => 'boolean'
        ];
    }
}
