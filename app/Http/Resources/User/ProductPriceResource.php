<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductPriceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'image' => $this->image?->getUrl(),
            'price' => formatMoney($this->pricable()->user_facing_price),
            'discount' => !$this->has_discount ? null : [
                'type' => $this->pricable()->discount_type,
                'value' => $this->pricable()->discount_value,
            ],
            'price_before_discount' => formatMoney($this->pricable()->price_before_discount),

        ];
    }

    // if product has variants, return the price of first visible variant value
    private function pricable()
    {
        if ($this->has_variants) {
            return $this->variant->firstVisibleValue;
        }

        return $this;
    }
}
