<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'product' => [
                'id' => $this->product->id,
                'name' => $this->product->name,
                'image' => $this->product->image->getUrl()
            ],
            'variant_value' => $this->when($this->variantValue, fn() => [
                'id' => $this->variantValue->id,
                'value' => $this->variantValue->value
            ]),
            'delivery_type' => $this->item->delivery_type->value,
            'price' => formatMoney($this->item->user_facing_price),
            'quantity' => $this->quantity,
        ];
    }
}
