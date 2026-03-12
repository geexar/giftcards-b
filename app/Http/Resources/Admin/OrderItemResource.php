<?php

namespace App\Http\Resources\Admin;

use App\Enums\OrderItemStatus;
use App\Http\Resources\CountryBasicResource;
use App\Services\Admin\CategoryService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product' => [
                'id' => $this->product->id,
                'name' => $this->product->name,
                'source' => $this->product->source->value,
            ],
            'variant_value' => !$this->variantValue ? null : [
                'id' => $this->variantValue->id,
                'value' => $this->variantValue->value,
            ],
            'delivery_type' => $this->delivery_type->value,
            'category' => app(CategoryService::class)->getCategoryHierarchy($this->product->category),
            'image' => $this->product->image->getUrl(),
            'price' => (string) $this->price,
            'quantity' => $this->quantity,
            'fulfilled_quantity' => $this->when($this->status == OrderItemStatus::PARTIALLY_FULFILLED, $this->fulfilled_quantity),
            'user_facing_price' => (string) $this->user_facing_price,
            'markup_fee' => [
                'origin' => $this->markup_fee_origin->value,
                'type' => $this->markup_fee_type,
                'value' => $this->markup_fee_value,
            ],
            'total' => (string) $this->total,
            'status' => $this->status?->value,
            'rejection_reason' => $this->when($this->status == OrderItemStatus::REJECTED, $this->rejection_reason),
            'is_global' => $this->product->is_global,
            'selected_countries' => $this->when(!$this->product->is_global, CountryBasicResource::collection($this->product->countries)),
            'codes' => $this->when(
                in_array($this->status, [OrderItemStatus::PARTIALLY_FULFILLED, OrderItemStatus::COMPLETED]),
                fn() => CodeResource::collection($this->codes)
            ),
        ];
    }
}
