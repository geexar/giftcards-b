<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemRatingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product' => [
                'id' => $this->orderItem->product->id,
                'name' => $this->orderItem->product->name
            ],
            'variant_value' => $this->when($this->orderItem->variantValue, fn() => [
                'id' => $this->orderItem->variantValue->id,
                'value' => $this->orderItem->variantValue->value
            ]),
            'rating' => formatRating($this->rating),
        ];
    }
}
