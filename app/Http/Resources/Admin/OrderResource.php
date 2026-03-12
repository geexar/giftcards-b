<?php

namespace App\Http\Resources\Admin;

use App\Enums\RefundStatus;
use App\Services\Admin\PaymentMethodService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $showRoute = $request->routeIs('admin.orders.show');

        return [
            'id' => $this->id,
            'order_no' => $this->order_no,
            'user' => !$this->user ? null : [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ],
            'guest' => $this->user ? null : [
                'id' => $this->id,
                'name' => $this->name,
                'email' => $this->email,
            ],
            'payment' => [
                'payment_method' => app(PaymentMethodService::class)->getPaymentMethodName($this->payment_method_id),
                "transaction_id" => $this->when($showRoute, fn() => $this->transaction?->id),
                "transaction_no" => $this->when($showRoute, fn() => $this->transaction?->transaction_no),
                'reference_id' => $this->when($showRoute && $this->payment_method_id == 2, fn() => $this->transaction?->reference_id),
                'total' => formatMoney($this->total),
                'profit' => $this->when($showRoute, fn() => formatMoney($this->transaction?->actual_profit)),
            ],
            'refund' => $this->when($showRoute && $this->refund, fn() => [
                'status' => $this->refund->status->value,
                'refund_amount' => formatMoney($this->refund->amount),
                'net_amount' => formatMoney($this->net_amount),
                'processed_by' => $this->when($this->refund->status == RefundStatus::PROCESSED, fn() => [
                    'id' => $this->refund->processor->id,
                    'name' => $this->refund->processor->name,
                ]),
                'processed_at' => $this->when($this->refund->status == RefundStatus::PROCESSED, fn() => formatDateTime($this->refund->processed_at)),
            ]),
            'is_gifted' => $this->is_gifted,
            'gifted_email' => $this->when($this->is_gifted, $this->gifted_email),
            'status' => $this->status,
            'refund_status' => $this->when(!$showRoute, $this->refund?->status->value),
            'items_count' => $this->items->sum('quantity'),
            'items' => $this->when($showRoute, fn() => OrderItemResource::collection($this->items)),
            'created_at' => formatDateTime($this->created_at),
            'updated_at' => formatDateTime($this->processed_at),
            'updated_by' => $this->when($showRoute && $this->processor, fn() => [
                'id' => $this->processor->id,
                'name' => $this->processor->name,
            ]),
            'rating' => [
                'avg' => formatRating($this->overallRating?->rating),
                'comment' => $this->when($showRoute, fn() => $this->overallRating?->comment),
                'created_at' => $this->when($showRoute, fn() => formatDateTime($this->overallRating?->created_at)),
                'ratings' => $this->when($showRoute, fn() => OrderItemRatingResource::collection($this->itemRatings->load('orderItem.product', 'orderItem.variantValue'))),
            ],
            'notes' => $this->when($showRoute, $this->notes),
            'activity_logs' => $this->when($showRoute, fn() => ActivityLogResource::collection($this->activityLogs)),
        ];
    }
}
