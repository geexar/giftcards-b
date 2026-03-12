<?php

namespace App\Http\Resources\User;

use App\Enums\OrderItemStatus;
use App\Models\Code;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'item_no' => $this->item_no,
            'product' => [
                'id' => $this->product->id,
                'name' => $this->product->name,
            ],
            'variant_value' => !$this->variantValue ? null : [
                'id' => $this->variantValue->id,
                'value' => $this->variantValue->value,
            ],
            'image' => $this->product->image->getUrl(),
            'price' => formatMoney($this->user_facing_price),
            'quantity' => $this->quantity,
            'fulfilled_quantity' => $this->when($this->status == OrderItemStatus::PARTIALLY_FULFILLED, $this->fulfilled_quantity),
            'total' => formatMoney($this->total),
            'status' => $this->status->value,
            'rejection_reason' => $this->when($this->status == OrderItemStatus::REJECTED, $this->rejection_reason),
            'can_cancel' => $this->can_cancel,
            'codes' => $this->when(!$this->order->is_gifted, CodeResource::collection($this->codes))
        ];
    }
}
