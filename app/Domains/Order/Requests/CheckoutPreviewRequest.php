<?php

namespace App\Domains\Order\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class CheckoutPreviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items'              => 'required|array|min:1',
            'items.*.variant_id' => 'nullable|exists:product_variants,id',
            'items.*.combo_id'   => 'nullable|exists:combos,id',
            'items.*.quantity'   => 'required|integer|min:1',

            'coupon_code' => 'nullable|string|max:50',
            'zone_id'     => 'nullable|exists:shipping_zones,id',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            foreach ($this->input('items', []) as $index => $item) {
                if (empty($item['variant_id']) && empty($item['combo_id'])) {
                    $v->errors()->add(
                        "items.{$index}",
                        'Each item must specify either a variant_id or a combo_id.'
                    );
                }
            }
        });
    }
}
