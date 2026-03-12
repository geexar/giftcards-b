<?php

namespace App\Http\Requests\Admin\Auth;

use App\Http\Requests\BaseFormRequest;

class LoginRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'remember_me' => ['boolean'],
            'fcm_token' => ['required_with:device_id', 'string', 'max:255'],
            'device_id' => ['required_with:fcm_token', 'string', 'max:255']
        ];
    }
}
