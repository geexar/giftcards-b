<?php

namespace App\Http\Requests\User\Auth;

use App\Rules\ValidPassword;
use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required_without:phone', 'string', 'email'],
            'otp' => ['required', 'string'],
            'password' => ['required', 'string', new ValidPassword, 'confirmed'],
        ];
    }
}
