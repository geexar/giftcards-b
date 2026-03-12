<?php

namespace App\Http\Requests\Admin;

use App\Enums\StockStatus;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rules\Enum;

class GetInventoryProducts extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'stock_status' => ['nullable', new Enum(StockStatus::class)],
        ];
    }
}
