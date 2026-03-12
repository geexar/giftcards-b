<?php

namespace App\Http\Requests\Admin\PaymentMethods;

use App\Http\Requests\BaseFormRequest;

class StripeSettingsRequest extends BaseFormRequest
{
    public function rules(): array
    {
        $rules = [
            'active_mode' => ['in:sandbox,live'],
            'active_for_checkout' => ['required', 'boolean'],
            'active_for_top_up' => ['required', 'boolean'],
        ];

        // webhook_secret
        foreach (['sandbox', 'live'] as $mode) {
            $rules["$mode.publishable_key"] = ["required_if:active_mode,{$mode}", 'string', 'max:255'];
            $rules["$mode.secret_key"] = ["required_if:active_mode,{$mode}", 'string', 'max:255'];
            $rules["$mode.webhook_secret"] = ["required_if:active_mode,{$mode}", 'string', 'max:255'];
        }

        return $rules;
    }

    public function attributes()
    {
        return [
            '*.publishable_key' => 'publishable key',
            '*.secret_key' => 'secret key',
            '*.webhook_secret' => 'webhook secret',
        ];
    }
}
