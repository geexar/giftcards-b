<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderRequest extends FormRequest
{
    public function rules(): array
    {
        $user = auth('user')->user();

        return [
            'send_as_gift' => ['required', 'boolean'],
            'gifted_email' => ['required_if:send_as_gift,1', 'email:rfc', 'max:255'],
            'guest_name' => [Rule::requiredIf((bool) !$user), 'string'],
            'guest_email' => [Rule::requiredIf((bool) !$user), 'email:rfc', 'max:255'],
            'payment_method' => ['required', 'string', 'in:card,wallet'],
        ];
    }
}
