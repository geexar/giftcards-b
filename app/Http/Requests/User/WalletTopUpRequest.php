<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseFormRequest;
use App\Rules\ValidAmount;

class WalletTopUpRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', new ValidAmount(), 'min:1', 'max:10000'],
        ];
    }
}
