<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UsdtAddressRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'network_id' => ['required', 'integer', 'exists:usdt_networks,id'],
        ];
    }
}
