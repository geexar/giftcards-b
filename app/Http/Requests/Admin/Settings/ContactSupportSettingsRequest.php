<?php

namespace App\Http\Requests\Admin\Settings;

use App\Http\Requests\BaseFormRequest;

class ContactSupportSettingsRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'max:255', 'email'],
            'whatsapp' => ['required', 'string', 'max:255', 'regex:/^\+\d{1,3}\d{6,14}$/'],
            'telegram' => ['required', 'string', 'min:1', 'max:255'],
        ];
    }

    public function messages()
    {
        return [
            'whatsapp.regex' => __('invalid number. please enter country code with number. e.g. +1234567890'),
        ];
    }
}
