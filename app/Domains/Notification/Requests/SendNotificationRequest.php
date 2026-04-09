<?php

namespace App\Domains\Notification\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendNotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('notification.send');
    }

    public function rules(): array
    {
        return [
            'recipient_type'  => 'required|in:all,role,specific',

            // Required when targeting roles or specific users
            'recipient_ids'   => 'required_if:recipient_type,role,specific|array|min:1',
            'recipient_ids.*' => 'required|string',

            'channels'        => 'required|array|min:1',
            'channels.*'      => 'required|string|in:mail,database',

            'subject'         => 'required|string|max:200',
            'message'         => 'required|string|max:5000',
        ];
    }

    public function messages(): array
    {
        return [
            'recipient_ids.required_if' => 'Please specify recipient IDs or role names.',
            'channels.required'         => 'Select at least one notification channel.',
            'channels.*.in'             => 'Invalid channel. Allowed: mail, database.',
        ];
    }
}
