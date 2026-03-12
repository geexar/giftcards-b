<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class GetOrdersRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'sort_by' => ['in:created_at,updated_at,total,rating']
        ];
    }
}
