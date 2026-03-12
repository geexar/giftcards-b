<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\BaseFormRequest;
use App\Rules\ValidMedia;
use App\Rules\ValidPassword;
use App\Rules\ValidPhone;
use Illuminate\Validation\Rule;

class AdminRequest extends BaseFormRequest
{
    public function rules(): array
    {
        $id = $this->route('admin');

        return [
            'name' => ['required', 'string', 'min:1', 'max:30'],
            'email' => ['required', 'string', 'email:rfc,dns', 'max:255', Rule::unique('admins', 'email')->ignore($id)],
            'country_code' => ['nullable', 'exists:countries,dial_code'],
            'phone' => ['nullable', 'string', new ValidPhone($this->country_code)],
            'password' => [Rule::requiredIf(!(bool) $id), 'nullable', 'string',  new ValidPassword, 'confirmed'],
            'is_active' => [Rule::requiredIf((bool) $id), 'boolean'],
            'role_id' => [Rule::requiredIf(!(bool) $id), 'exists:roles,id'],
            'permissions' => ['required', 'array', 'min:1'],
            'permissions.*' => ['integer'],
            'image' => ['nullable', new ValidMedia(['image'])]

        ];
    }
}
