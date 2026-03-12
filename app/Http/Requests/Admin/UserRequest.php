<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\BaseFormRequest;
use App\Rules\ValidMedia;
use App\Rules\ValidPassword;
use App\Rules\ValidPhone;
use Illuminate\Validation\Rule;

class UserRequest extends BaseFormRequest
{
    public function rules(): array
    {
        $id = $this->route('user');

        return [
            'name' => ['required', 'string', 'min:1', 'max:30'],
            'email' => ['required', 'string', 'email:rfc,dns', 'max:255', Rule::unique('users', 'email')->ignore($id)],
            'country_code' => ['nullable', 'exists:countries,dial_code'],
            'phone' => ['nullable', 'string', new ValidPhone($this->country_code)],
            'password' => [Rule::requiredIf(!(bool) $id), 'nullable', 'string',  new ValidPassword, 'confirmed'],
            'is_active' => [Rule::requiredIf((bool) $id), 'boolean'],
            'image' => ['nullable', new ValidMedia(['image'])]
        ];
    }
}
