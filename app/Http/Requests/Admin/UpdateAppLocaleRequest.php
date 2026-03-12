<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAppLocaleRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'app_locale' => ['required', 'in:en,ar'],
        ];
    }
}
