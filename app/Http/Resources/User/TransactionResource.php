<?php

namespace App\Http\Resources\User;

use App\Enums\TransactionType;
use App\Services\Admin\PaymentMethodService;
use App\Services\Admin\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'transaction_no' => $this->transaction_no,
            'type' => $this->type->value,
            'amount' => $this->amount,
            'manual_adjustment_reason' => $this->when($this->type == TransactionType::MANUAL_ADJUSTMENT, fn() => $this->description),
            'manual_adjustment_type' => $this->when(
                $this->type == TransactionType::MANUAL_ADJUSTMENT,
                fn() => app(TransactionService::class)->getManualAdjustmentType($this->resource)
            ),
            'order_no' => $this->when($this->type == TransactionType::PURCHASE, fn() => $this->order?->order_no),
            'payment_method' => $this->when(
                in_array($this->type, [TransactionType::TOPUP, TransactionType::PURCHASE]),
                fn() => app(PaymentMethodService::class)->getPaymentMethodName($this->payment_method_id)
            ),
            'usdt_network' => $this->when(
                $this->type == TransactionType::TOPUP && $this->payment_method_id == 3,
                fn() => $this->usdt_network
            ),
            'created_at' => formatDateTime($this->created_at)
        ];
    }
}
