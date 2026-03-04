<?php

namespace App\Domains\Product\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'q' => 'nullable|string|max:100',

            'category_id' => 'nullable|exists:categories,id',

            'min_price' => 'nullable|numeric|min:0',

            'max_price' => 'nullable|numeric|min:0',

            'featured' => 'nullable|boolean',

            'sort' => 'nullable|in:price_asc,price_desc,latest',

            'page' => 'nullable|integer'
        ];
    }
}
