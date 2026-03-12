<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class CountryRequest extends BaseFormRequest
{
    public function rules(): array
    {
        $id = $this->route('country');

        $rules = [];

        // Translated names
        foreach (config('app.locales') as $locale) {
            $rules["name.{$locale}"] = [
                'required',
                'string',
                'min:2',
                'max:100',
                Rule::unique('countries', "name->{$locale}")->ignore($id),
            ];
        }

        $rules['is_active'] = ['required', 'boolean'];

        return $rules;
    }
}
