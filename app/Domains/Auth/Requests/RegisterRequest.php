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
      'name'     => 'required|string|max:150',
      'phone'    => 'required|string|unique:users,phone|regex:/^([0-9\s\-\+\(\)]*)$/|min:10',
      'email'    => 'nullable|email|max:255|unique:users,email',
      'password' => 'required|string|min:6|confirmed',
    ];
  }

  public function messages(): array
  {
    return [
      'phone.unique' => 'This phone number is already registered.',
      'email.unique' => 'This email address is already registered.',
      'password.confirmed' => 'The password confirmation does not match.',
      'phone.regex' => 'The phone number format is invalid.',
    ];
  }
}
