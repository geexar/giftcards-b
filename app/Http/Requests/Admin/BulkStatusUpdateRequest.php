<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class BulkStatusUpdateRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'status' => ['required', 'in:active,disabled'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', Rule::exists('products', 'id')->whereNull('deleted_at')],
        ];
    }
}
