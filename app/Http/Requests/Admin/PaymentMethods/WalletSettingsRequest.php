<?php

namespace App\Http\Requests\Admin\PaymentMethods;

use App\Http\Requests\BaseFormRequest;

class WalletSettingsRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'is_active' => ['required', 'boolean']
        ];
    }
}
