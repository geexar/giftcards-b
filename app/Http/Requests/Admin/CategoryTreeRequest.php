<?php

namespace App\Http\Requests\Admin;

use App\Enums\CategoryType;
use App\Enums\ProductSource;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rules\Enum;

class CategoryTreeRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'type' => ['required', new Enum(CategoryType::class)],
            'is_active' => ['boolean'],
            'products_count_source' => [new Enum(ProductSource::class)],
        ];
    }
}
