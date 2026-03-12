<?php

namespace App\Http\Requests\Admin\Settings;

use App\Http\Requests\BaseFormRequest;

class InventorySettingsRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'stock_threshold' => ['required', 'integer', 'min:0', 'max:1000000']
        ];
    }
}
