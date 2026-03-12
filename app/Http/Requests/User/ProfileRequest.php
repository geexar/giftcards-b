<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseFormRequest;
use App\Models\Admin;
use App\Rules\UniquePhone;
use App\Rules\ValidMedia;
use App\Rules\ValidPhone;

class ProfileRequest extends BaseFormRequest
{
    public function rules(): array
    {
        $id = auth('user')->id();

        return [
            'name' => ['required', 'string', 'min:3', 'max:30'],
            'country_code' => ['nullable', 'exists:countries,dial_code'],
            'phone' => ['nullable', 'string', new ValidPhone($this->country_code), new UniquePhone($this->country_code, Admin::class, $id)],
            'image' => ['nullable', new ValidMedia(['image'])]
        ];
    }
}
