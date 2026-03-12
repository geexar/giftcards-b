<?php

namespace App\Http\Requests\User\Auth;

use App\Http\Requests\BaseFormRequest;

class AddAppleEmailRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'token' => ['required', 'string'],
            'email' => ['required', 'email:rfc,dns', 'max:255'],
            'fcm_token' => ['required_with:device_id', 'string', 'max:255'],
            'device_id' => ['required_with:fcm_token', 'string', 'max:255']
        ];
    }
}
