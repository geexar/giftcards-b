<?php

namespace App\Http\Requests\Admin\Settings;

use App\Http\Requests\BaseFormRequest;
use App\Rules\ValidAmount;
use App\Rules\ValidAmountByType;

class MarkupFeeSettingsRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'markup_fee_type' => ['required', 'in:fixed,percentage'],
            'markup_fee_value' => ['required', 'min:0', new ValidAmount(), new ValidAmountByType($this->input('markup_fee_type'))]
        ];
    }
}
