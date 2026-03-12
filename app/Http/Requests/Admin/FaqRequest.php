<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class FaqRequest extends BaseFormRequest
{
    public function rules(): array
    {
        $id = $this->route('faq');

        $rules = [
            'is_active' => [Rule::requiredIf((bool) $id), 'boolean'],
        ];

        foreach (config('app.locales') as $locale) {
            $rules["question.{$locale}"] = [
                'required',
                'string',
                'min:3',
                'max:200',
                Rule::unique('faqs', "question->{$locale}")->ignore($id)
            ];

            $rules["answer.{$locale}"] = [
                'required',
                'string',
                'min:3',
                'max:1000'
            ];
        }

        return $rules;
    }
}
