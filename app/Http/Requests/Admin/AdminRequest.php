<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\BaseFormRequest;
use App\Models\Admin;
use App\Rules\ActiveRole;
use App\Rules\UniquePhone;
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
            'name' => ['required', 'string', 'min:3', 'max:50'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('admins', 'email')->ignore($id)],
            'country_code' => ['required_with:phone', 'exists:countries,dial_code'],
            'phone' => ['required_with:country_code', 'string', new ValidPhone($this->country_code), new UniquePhone($this->country_code, Admin::class, $id)],
            'password' => [Rule::requiredIf(!(bool) $id), 'string',  new ValidPassword(), 'confirmed'],
            'is_active' => [Rule::requiredIf((bool) $id), 'boolean'],
            'role_id' => [Rule::requiredIf(!(bool) $id), 'exists:roles,id', new ActiveRole()],
            'permissions' => ['required', 'array', 'min:1'],
            'permissions.*' => ['integer'],
            'image' => ['nullable', new ValidMedia(['image'])]
        ];
    }
}
