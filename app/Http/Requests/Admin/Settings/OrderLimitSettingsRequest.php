<?php

namespace App\Http\Requests\Admin\Settings;

use App\Http\Requests\BaseFormRequest;

class OrderLimitSettingsRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'max_units_per_order' => ['required', 'integer', 'min:1', 'max:1000000']
        ];
    }
}
