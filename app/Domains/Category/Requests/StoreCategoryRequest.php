<?php

namespace App\Domains\Category\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('category.create');
    }

    public function rules(): array
    {
        return [
            'name'        => 'required|string|max:150|unique:categories,name',
            'description' => 'nullable|string',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048', // 2MB Max
            'is_active'   => 'boolean',
            'sort_order'  => 'nullable|integer'
        ];
    }
}
