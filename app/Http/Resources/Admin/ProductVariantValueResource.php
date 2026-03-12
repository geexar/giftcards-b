<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantValueResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $showRoute = $request->routeIs('admin.products.show');

        return [
            'id' => $this->id,
            'value' => $this->value,
            'description' => $this->when($showRoute, fn() => (object) $this->getTranslations('description')),
            'base_price' => $this->when($showRoute, formatMoney($this->base_price)),
            'final_price' => formatMoney($this->final_price),
            'user_facing_price' => formatMoney($this->user_facing_price),
            'discount' => $this->when($showRoute, [
                'has_discount' => $this->has_discount,
                'discount_type' => $this->when($this->has_discount, $this->discount_type),
                'discount_value' => $this->when($this->has_discount, formatMoney($this->discount_value)),
            ]),
            'delivery_type' => $this->when($showRoute, fn() => $this->delivery_type),
            'is_visible' => $this->when($showRoute, $this->is_visible),
            'marked_as_out_of_stock' => $this->when($showRoute, $this->marked_as_out_of_stock),
            'quantity' => $this->when($showRoute, $this->stock),
            'in_stock' => $this->in_stock,
            'unused_codes' => $this->when($showRoute, fn() => CodeResource::collection($this->validCodes)),
            'expired_codes' => $this->when($showRoute, fn() => CodeResource::collection($this->expiredCodes)),
        ];
    }
}
