<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseFormRequest;
use App\Rules\ValidPhone;

class ContactMessageRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:1', 'max:255'],
            'email' => ['required', 'email:rfc,dns', 'max:255'],
            'country_code' => ['required_with:phone', 'exists:countries,dial_code'],
            'phone' => ['required_with:country_code', 'string', new ValidPhone($this->country_code), 'max:255'],
            'message' => ['required', 'string', 'min:1', 'max:1000'],
        ];
    }
}
