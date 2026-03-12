<?php

namespace App\Http\Requests\User\Auth;

use App\Models\User;
use App\Rules\UniquePhone;
use App\Rules\ValidPassword;
use App\Rules\ValidPhone;
use Illuminate\Foundation\Http\FormRequest;

class RegistrationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:50'],
            'email' => ['required', 'string', 'email:rfc,dns', 'unique:users,email', 'max:255'],
            'country_code' => ['required_with:phone', 'exists:countries,dial_code'],
            'phone' => ['nullable', 'string', new ValidPhone($this->country_code), new UniquePhone($this->country_code, User::class)],
            'password' => ['required', 'string', new ValidPassword, 'confirmed'],
            'otp' => ['required', 'string'],
            'fcm_token' => ['required_with:device_id', 'string', 'max:255'],
            'device_id' => ['required_with:fcm_token', 'string', 'max:255']
        ];
    }
}
