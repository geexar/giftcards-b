<?php

namespace App\Http\Resources\User;

use App\Services\User\ProductAvailabilitySubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantValueResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'value' => $this->value,
            'delivery_type' => $this->delivery_type->value,
            'description' => $this->description,
            'rating' => formatRating($this->avg_rating),
            'price' => formatMoney($this->user_facing_price),
            'discount' => !$this->has_discount ? null : [
                'type' => $this->discount_type,
                'value' => $this->discount_value
            ],
            'price_before_discount' => $this->when($this->has_discount, fn() => formatMoney($this->price_before_discount)),
            'in_stock' => $this->has_available_stock && !$this->marked_as_out_of_stock,
            'subscribed_to_stock_availability' => $this->when(
                !$this->has_variants,
                fn() => app(ProductAvailabilitySubscriptionService::class)->isUserAlreadySubscribedToVariantValue($this->id, auth('user')->id())
            ),
        ];
    }
}
