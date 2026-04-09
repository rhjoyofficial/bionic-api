<?php

namespace App\Domains\Coupon\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkGenerateCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('coupon.create');
    }

    public function rules(): array
    {
        return [
            'prefix'         => 'required|string|max:10|alpha_num',
            'count'          => 'required|integer|min:1|max:500',
            'type'           => 'required|in:fixed,percentage',
            'value'          => 'required|numeric|min:0',
            'min_purchase'   => 'nullable|numeric|min:0',
            'usage_limit'    => 'nullable|integer|min:1',
            'limit_per_user' => 'nullable|integer|min:1',
            'start_date'     => 'nullable|date',
            'end_date'       => 'nullable|date|after_or_equal:start_date',
            'is_active'      => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'prefix.alpha_num' => 'Prefix must contain only letters and numbers.',
            'count.max'        => 'Cannot generate more than 500 coupons at once.',
        ];
    }
}
