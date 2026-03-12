<?php

namespace App\Http\Requests\Admin;

use App\Rules\ValidAmount;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserBalanceRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'type' => ['required', 'in:add,deduct'],
            'amount' => ['required', 'numeric', new ValidAmount(), 'min:0.1', 'max:1000000'],
            'description' => ['required', 'string', 'min:1', 'max:255'],
        ];
    }
}
