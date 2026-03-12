<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class SubscribeProductAvailabilityRequest extends BaseFormRequest
{
    public function rules(): array
    {
        $user = auth('user')->user();

        return [
            'email' => [Rule::requiredIf((bool) !$user), 'email', 'max:255'],
            'type' => ['required', 'in:product,variant'],
            'id' => ['required', 'string'],
        ];
    }
}
