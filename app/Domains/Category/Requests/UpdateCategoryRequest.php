<?php

namespace App\Domains\Category\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('category.update');
    }

    public function rules(): array
    {
        $categoryId = $this->category->id;
        return [
            'name'        => 'sometimes|string|max:150|unique:categories,name,' . $categoryId,
            'description' => 'nullable|string',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_active'   => 'boolean',
            'sort_order'  => 'nullable|integer'
        ];
    }
}
