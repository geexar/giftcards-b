<?php

namespace App\Http\Resources\Admin;

use App\Enums\TransactionType;
use App\Services\Admin\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'transaction_no' => $this->transaction_no,
            'type' => $this->type->value,
            'actor_type' => app(TransactionService::class)->getActorType($this->resource),
            'actor' => $this->when($this->actor, fn() => [
                'id' => $this->actor->id,
                'name' => $this->actor->name,
            ]),
            'amount' => $this->amount,
            'projected_profit' => $this->projected_profit,
            'actual_profit' => $this->actual_profit,
            'reference_id' => $this->reference_id,
            'order_id' => $this->when(in_array($this->type, [TransactionType::PURCHASE, TransactionType::REFUND]), fn() => $this->order_id),
            'order_no' => $this->when(in_array($this->type, [TransactionType::PURCHASE, TransactionType::REFUND]), fn() => $this->order->order_no),
            'payment_method' => $this->when($this->type == TransactionType::TOPUP, fn() => $this->paymentMethod->name),
            'usdt_network' => $this->when($this->type == TransactionType::TOPUP && $this->payment_method_id == 3, fn() => $this->usdt_network),
            'manual_adjustment_type' => $this->when($this->type == TransactionType::MANUAL_ADJUSTMENT, fn() => app(TransactionService::class)->getManualAdjustmentType($this->resource)),
            'manual_adjustment_reason' => $this->when($this->type == TransactionType::MANUAL_ADJUSTMENT, fn() => $this->description),
            'status' => $this->status,
            'created_at' => formatDateTime($this->created_at)
        ];
    }
}
