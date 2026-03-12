<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class GroupedNotificationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:1', 'max:255'],
            'body' => ['required', 'string', 'min:1', 'max:255'],
            'sent_to_all' => ['required', 'boolean'],
            'selected_users' => ['required_if:sent_to_all,0', 'array', 'min:1'],
            'selected_users.*' => ['exists:users,id']
        ];
    }
}
