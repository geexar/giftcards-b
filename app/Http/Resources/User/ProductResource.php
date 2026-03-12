<?php

namespace App\Http\Resources\User;

use App\Services\Admin\CategoryService;
use App\Services\Admin\MarkupFeeService;
use App\Services\User\ProductAvailabilitySubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'image' => $this->image?->getUrl(),
            'rating' => $this->when($this->has_variants, fn() => formatRating($this->avg_rating)),
            'is_global' => $this->is_global,
            'countries' => $this->when(!$this->is_global, $this->countries->pluck('name')->toArray()),
            'delivery_type' => $this->when(!$this->has_variants, fn() => $this->delivery_type->value),
            'short_description' => $this->short_description,
            'description' => $this->description,
            'in_stock' => $this->when(!$this->has_variants, ($this->has_available_stock && !$this->marked_as_out_of_stock)),
            'price' => $this->when(!$this->has_variants, fn() => formatMoney($this->user_facing_price)),
            'discount' => $this->when(!$this->has_variants, !$this->has_discount ? null : [
                'type' => $this->discount_type,
                'value' => $this->discount_value
            ]),
            'price_before_discount' => $this->when(!$this->has_variants && $this->has_discount, fn() => formatMoney($this->price_before_discount)),
            'category' => app(CategoryService::class)->getCategoryHierarchy($this->category),
            'variant' => $this->when($this->has_variants, fn() => [
                'id' => $this->variant->id,
                'name' => $this->variant->name,
                'values' => ProductVariantValueResource::collection($this->variant->visibleValues),
            ]),
            'subscribed_to_stock_availability' => $this->when(
                !$this->has_variants,
                fn() => app(ProductAvailabilitySubscriptionService::class)->isUserAlreadySubscribedToProduct($this->id, auth('user')->id())
            ),
        ];
    }
}
