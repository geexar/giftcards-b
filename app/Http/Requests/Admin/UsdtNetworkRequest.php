<?php

namespace App\Http\Requests\Admin;

use App\Rules\ValidAmount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UsdtNetworkRequest extends FormRequest
{
    public function rules(): array
    {
        $id = $this->route('usdt_network');

        return [
            'identifier' => [Rule::requiredIf((bool) !$id), 'string', 'min:1', 'max:50', Rule::unique('usdt_networks', 'identifier')->ignore($id)],
            'name' => ['required', 'string', 'min:3', 'max:50', Rule::unique('usdt_networks', 'name')->ignore($id)],
            'fixed_fees' => ['required', 'numeric', 'min:0', 'max:1000000', new ValidAmount()],
            'percentage_fees' => ['required', 'numeric', 'min:0', 'max:100', new ValidAmount()],
            'is_active' => [Rule::requiredIf((bool) $id), 'boolean'],
        ];
    }
}
