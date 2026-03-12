<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class GetAvailablePaymentMethodsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'type' => ['required', 'in:checkout,top_up'],
        ];
    }
}
