<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleRequest extends FormRequest
{
    public function rules(): array
    {
        $id = $this->route('role');

        return [
            'name' => ['required', 'string', 'min:3', 'max:30', Rule::unique('roles', 'name')->ignore($id)],
            'is_active' => [Rule::requiredIf((bool) $id), 'boolean'],
            'permissions' => ['required', 'array', 'min:1'],
            'permissions.*' => ['integer']
        ];
    }
}
