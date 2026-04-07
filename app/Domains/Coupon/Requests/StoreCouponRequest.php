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
            'code'             => 'required|string|unique:coupons,code|max:50',
            'type'             => 'required|in:fixed,percentage',
            'value'            => 'required|numeric|min:0',
            'min_purchase'     => 'nullable|numeric|min:0',
            'usage_limit'      => 'nullable|integer|min:1',
            'limit_per_user'   => 'nullable|integer|min:1',
            'start_date'       => 'nullable|date|after_or_equal:today',
            'end_date'         => 'nullable|date|after:start_date',
            'is_active'        => 'boolean'
        ];
    }
}
