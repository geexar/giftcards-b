<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class UpdateEmailRequest extends BaseFormRequest
{
    public function rules(): array
    {
        $id = auth('user')->id();

        return [
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($id)],
            'password' => ['required', 'string'],
            'otp' => ['required', 'string'],
        ];
    }
}
