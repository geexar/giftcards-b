<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;
use App\Rules\ValidMedia;

class CategoryRequest extends BaseFormRequest
{
    public function rules(): array
    {
        $id = $this->route('category');

        $rules = [
            'parent_id' => ['nullable', 'exists:categories,id', Rule::notIn([$id])], // Prevent assigning self as parent
            'is_active' => [Rule::requiredIf((bool) $id), 'boolean'],
            'image' => [Rule::requiredIf((bool) !$id), new ValidMedia(['image'])],
            'is_promoted' => ['boolean'],
            'is_featured' => ['boolean'],
            'is_trending' => ['boolean'],
        ];

        foreach (config('app.locales') as $locale) {
            $rules["name.{$locale}"] = [
                'required',
                'string',
                'min:3',
                'max:200',
                Rule::unique('categories', "name->{$locale}")->ignore($id),
            ];

            $rules["short_description.{$locale}"] = [
                'nullable',
                'string',
                'min:3',
                'max:300',
            ];

            $rules["description.{$locale}"] = [
                'nullable',
                'string',
                'min:10',
                'max:1000',
            ];
        }

        return $rules;
    }
}
