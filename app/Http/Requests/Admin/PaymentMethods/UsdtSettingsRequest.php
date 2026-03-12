<?php

namespace App\Http\Requests\Admin\PaymentMethods;

use App\Http\Requests\BaseFormRequest;

class UsdtSettingsRequest extends BaseFormRequest
{
    public function rules(): array
    {
        $rules = [
            'active_mode' => ['in:sandbox,live'],
            'is_active' => ['required', 'boolean'],
        ];

        foreach (['sandbox', 'live'] as $mode) {
            $rules["{$mode}.app_id"] = ["required_if:active_mode,{$mode}", 'string', 'max:255'];
            $rules["{$mode}.app_secret"] = ["required_if:active_mode,{$mode}", 'string', 'max:255'];
        }

        return $rules;
    }

    public function attributes()
    {
        return [
            '*.app_id' => 'app id',
            '*.app_secret' => 'app secret',
        ];
    }
}
