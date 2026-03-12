<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;
use App\Rules\ValidMedia;

class ArticleRequest extends BaseFormRequest
{
    public function rules(): array
    {
        $id = $this->route('article');

        $rules = [
            'is_active' => [Rule::requiredIf((bool) $id), 'boolean'],
            'image' => [Rule::requiredIf((bool) !$id), new ValidMedia(['image'])]
        ];

        foreach (config('app.locales') as $locale) {
            $rules["title.{$locale}"] = [
                'required',
                'string',
                'min:3',
                'max:200',
                Rule::unique('articles', "title->{$locale}")->ignore($id),
            ];

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
