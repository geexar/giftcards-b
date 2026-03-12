<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseFormRequest;

class CartItemRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'type' => ['required', 'in:product,variant'],
            'id' => ['required', 'string'],
        ];
    }
}
