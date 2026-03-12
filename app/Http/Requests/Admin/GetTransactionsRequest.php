<?php

namespace App\Http\Requests\Admin;

use App\Enums\TransactionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class GetTransactionsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'type' => [new Enum(TransactionType::class)],
            'actor_type' => ['in:system,admin,user'],
            'sort_by' => ['in:created_at,amount,actual_profit',],
            'sort_order' => ['required_with:sort_by', 'in:asc,desc']
        ];
    }
}
