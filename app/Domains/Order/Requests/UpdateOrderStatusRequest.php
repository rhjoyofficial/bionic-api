<?php

namespace App\Domains\Order\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('order.update');
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled,returned'
        ];
    }
}
