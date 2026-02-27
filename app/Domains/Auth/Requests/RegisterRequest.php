<?php

namespace App\Domains\Auth\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'name' => 'required|string|max:150',
      'phone' => 'required|string|unique:users,phone',
      'email' => 'nullable|email|unique:users,email',
      'password' => 'required|string|min:6'
    ];
  }
}
