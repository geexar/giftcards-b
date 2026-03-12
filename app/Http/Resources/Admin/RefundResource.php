<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RefundResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'refund_no' => $this->refund_no,
            'order' => [
                'id' => $this->order->id,
                'order_no' => $this->order->order_no,
                'status' => $this->order->status->value
            ],
            'can_make_processed' => $this->can_make_processed,
            'amount' => $this->amount,
            'status' => $this->status,
            'customer_name' => $this->order->user->name ?? $this->order->name,
            'processed_by' => $this->when($this->processed_by, fn() => [
                'id' => $this->processor->id,
                'name' => $this->processor->name
            ]),
            'processed_at' => $this->when($this->processed_by, fn() => formatDateTime($this->processed_at)),
            'created_at' => formatDateTime($this->created_at),
            'notes' => $this->notes,
        ];
    }
}
