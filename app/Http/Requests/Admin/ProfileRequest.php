<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\BaseFormRequest;
use App\Models\Admin;
use App\Rules\UniquePhone;
use App\Rules\ValidMedia;
use App\Rules\ValidPassword;
use App\Rules\ValidPhone;
use Illuminate\Validation\Rule;

class ProfileRequest extends BaseFormRequest
{
    public function rules(): array
    {
        $id = auth('admin')->id();

        return [
            'name' => ['required', 'string', 'min:3', 'max:30'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('admins', 'email')->ignore($id)],
            'country_code' => ['nullable', 'exists:countries,dial_code'],
            'phone' => ['nullable', 'string', new ValidPhone($this->country_code), new UniquePhone($this->country_code, Admin::class, $id), ],
            'current_password' => ['required_with:password', 'current_password:admin'],
            'password' => ['nullable', 'string',  new ValidPassword(), 'confirmed'],
            'image' => ['nullable', new ValidMedia(['image'])]
        ];
    }
}
