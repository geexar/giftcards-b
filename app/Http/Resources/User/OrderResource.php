<?php

namespace App\Http\Resources\User;

use App\Enums\OrderItemStatus;
use App\Services\Admin\PaymentMethodService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $showRoute = $request->routeIs('user.orders.show');

        return [
            'order_no' => $this->order_no,
            'payment_method' => app(PaymentMethodService::class)->getPaymentMethodName($this->payment_method_id),
            'total' => formatMoney($this->total),
            'refund' => $this->when($this->refund, fn() => [
                'amount' => formatMoney($this->refund->amount),
                'status' => $this->refund->status->value
            ]),
            'is_gifted' => $this->is_gifted,
            'gifted_email' => $this->when($this->is_gifted, $this->gifted_email),
            'status' => $this->status,
            'items_count' => $this->items->sum('quantity'),
            'items' => $this->when($showRoute, fn() => OrderItemResource::collection($this->getSortedItems())),
            'created_at' => formatDateTime($this->created_at),
            'images' => $this->when(!$showRoute, fn() => $this->getOrderImages()),
            'can_cancel' => $this->can_cancel,
            'can_rate' => $this->can_rate
        ];
    }

    private function getSortedItems()
    {
        $moveToEnd = [
            OrderItemStatus::FAILED,
            OrderItemStatus::REJECTED,
        ];

        return $this->items
            ->sortBy(function ($item) use ($moveToEnd) {
                return in_array($item->status, $moveToEnd) ? 1 : 0;
            })
            ->values();
    }

    private function getOrderImages()
    {
        return $this->items->pluck('product.image')->map(fn($image) => $image->getUrl())->values();
    }
}
