<?php

namespace App\Domains\Coupon\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('coupon.update');
    }

    public function rules(): array
    {
        return [
            'type'           => 'sometimes|in:percentage,fixed',
            'value'          => 'sometimes|numeric|min:0',
            'min_purchase'   => 'nullable|numeric|min:0',
            'usage_limit'    => 'nullable|integer|min:1',
            'limit_per_user' => 'nullable|integer|min:1',
            'start_date'     => 'nullable|date',
            'end_date'       => 'nullable|date|after_or_equal:start_date',
            'is_active'      => 'boolean',
        ];
    }
}
