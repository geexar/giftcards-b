<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\BaseFormRequest;

class StaticPageRequest extends BaseFormRequest
{
    public function rules(): array
    {
        $rules = [];

        foreach (config('app.locales') as $locale) {
            $rules["body.{$locale}"] = [
                'required',
                'string',
                'min:3',
                'max:5000',
            ];
        }

        return $rules;
    }
}
