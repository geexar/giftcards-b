<?php

namespace App\Http\Requests\User\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
            'remember_me' => ['boolean'],
            'fcm_token' => ['required_with:device_id', 'string', 'max:255'],
            'device_id' => ['required_with:fcm_token', 'string', 'max:255']
        ];
    }
}
