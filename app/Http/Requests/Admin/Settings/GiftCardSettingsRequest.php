<?php

namespace App\Http\Requests\Admin\Settings;

use App\Http\Requests\BaseFormRequest;

class GiftCardSettingsRequest extends BaseFormRequest
{
    public function rules(): array
    {
        $rules = [
            'active_mode' => ['in:sandbox,live'],
            'base_price_source' => ['in:sell_price,product_price'],
        ];

        foreach (['sandbox', 'live'] as $mode) {
            $rules["$mode.device_id"] = ["required_if:active_mode,{$mode}", 'nullable', 'string', 'max:255'];
            $rules["$mode.email"] = ["required_if:active_mode,{$mode}", 'nullable', 'string', 'max:255', 'email:rfc,dns'];
            $rules["$mode.security_code"] = ["required_if:active_mode,{$mode}", 'nullable', 'string', 'max:255'];
            $rules["$mode.phone"] = ["required_if:active_mode,{$mode}", 'nullable', 'string', 'min:5', 'max:15', 'regex:/^\d+$/'];
            $rules["$mode.hash_key"] = ["required_if:active_mode,{$mode}", 'nullable', 'string', 'max:255'];
            $rules["$mode.secret_key"] = ["required_if:active_mode,{$mode}", 'nullable', 'string', 'max:255'];
            $rules["$mode.secret_iv"] = ["required_if:active_mode,{$mode}", 'nullable', 'string', 'max:255'];
        }

        return $rules;
    }

    public function attributes()
    {
        return [
            '*.device_id' => 'device id',
            '*.email' => 'email',
            '*.security_code' => 'security code',
            '*.phone' => 'phone',
            '*.hash_key' => 'hash key',
            '*.secret_key' => 'secret key',
            '*.secret_iv' => 'secret iv',
        ];
    }
}
