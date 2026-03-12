<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class OrderNotesRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'notes' => ['required', 'string', 'max:255'],
        ];
    }
}
