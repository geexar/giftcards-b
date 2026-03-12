<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseFormRequest;

class RateOrderRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'comment' => ['string', 'max:255'],
            'items' => ['array', 'min:1'],
            'items.*.item_no' => ['required', 'string', 'exists:order_items,item_no'],
            'items.*.rating' => ['required', 'integer', 'min:1', 'max:5'],
        ];
    }
}
