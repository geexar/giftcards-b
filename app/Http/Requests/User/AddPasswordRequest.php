<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseFormRequest;
use App\Rules\ValidPassword;

class AddPasswordRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'password' => ['required', 'string',  new ValidPassword(), 'confirmed'],
        ];
    }
}
