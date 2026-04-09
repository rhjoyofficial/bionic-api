<?php

namespace App\Domains\Order\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('order.update');
    }

    public function rules(): array
    {
        return [
            'payment_status'        => 'required|in:unpaid,paid,failed',
            'gateway_transaction_id' => 'nullable|string|max:255',
            'note'                  => 'nullable|string|max:500',
        ];
    }
}
