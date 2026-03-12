<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseFormRequest;
use App\Rules\ValidPassword;

class UpdatePasswordRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'current_password:user'],
            'password' => ['required', 'string',  new ValidPassword(), 'confirmed'],
        ];
    }
}
