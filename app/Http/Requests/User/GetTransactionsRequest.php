<?php

namespace App\Http\Requests\User;

use App\Enums\TransactionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GetTransactionsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'type' => [
                Rule::in([
                    TransactionType::TOPUP->value,
                    TransactionType::PURCHASE->value,
                    TransactionType::MANUAL_ADJUSTMENT->value,
                ]),
            ],
        ];
    }
}
