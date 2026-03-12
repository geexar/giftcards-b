<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseFormRequest;
use App\Rules\ValidMedia;

class UpdateImageRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'image' => ['required', new ValidMedia(['image'])]
        ];
    }
}
