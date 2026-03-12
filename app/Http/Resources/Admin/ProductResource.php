<?php

namespace App\Http\Resources\Admin;

use App\Enums\DeliveryType;
use App\Enums\ProductSource;
use App\Http\Resources\CountryBasicResource;
use App\Services\Admin\CategoryService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $showRoute = $request->routeIs('admin.products.show');
        $showCodes = $showRoute && !$this->has_variants && $this->source == ProductSource::LOCAL && $this->delivery_type == DeliveryType::INSTANT;
        $showStock = $showRoute && !$this->has_variants && $this->source == ProductSource::LOCAL;

        return [
            'id' => $this->id,
            'source' => $this->source->value,
            'name' => $showRoute ? $this->getTranslations('name') : $this->name,
            'short_description' => $this->when($showRoute, fn() => (object) $this->getTranslations('short_description')),
            'description' => $this->when($showRoute, fn() => (object) $this->getTranslations('description')),
            'category' => $this->when($showRoute, app(CategoryService::class)->getCategoryHierarchy($this->category)),
            'status' => $this->status->value,
            'is_global' => $this->when($showRoute, $this->is_global),
            'selected_countries' => $this->when($showRoute && !$this->is_global, CountryBasicResource::collection($this->countries)),
            'is_best_seller' => $this->when($showRoute, $this->is_best_seller),
            'is_popular' => $this->when($showRoute, $this->is_popular),
            'is_featured' => $this->when($showRoute, $this->is_featured),
            'is_trending' => $this->when($showRoute, $this->is_trending),
            'variant' => $this->when($this->has_variants, fn() => [
                'id' => $this->variant->id,
                'name' => $this->variant->name,
                'values' => ProductVariantValueResource::collection($this->variant->values),
            ]),
            'status' => $this->status->value,
            'delivery_type' => $this->when(!$this->has_variants, fn() => $this->delivery_type),
            'base_price' => $this->when($showRoute && !$this->has_variants, fn() => formatMoney($this->base_price)),
            'final_price' => $this->when(!$this->has_variants, fn() => formatMoney($this->final_price)),
            'markup_fee' => $this->when(!$showRoute, $this->indexMarkupFee()),
            'user_facing_price' => $this->when(!$this->has_variants, fn() => formatMoney($this->user_facing_price)),
            'discount' => $this->when($showRoute && !$this->has_variants, [
                'has_discount' => $this->has_discount,
                'type' => $this->when($this->has_discount, $this->discount_type),
                'value' => $this->when($this->has_discount, formatMoney($this->discount_value)),
            ]),
            'custom_markup_fee' => $this->when($showRoute, [
                'has_custom_markup_fee' => $this->has_custom_markup_fee,
                'type' => $this->when($this->has_custom_markup_fee, $this->custom_markup_fee_type),
                'value' => $this->when($this->has_custom_markup_fee, formatMoney($this->custom_markup_fee_value)),
            ]),
            'in_stock' => $this->when(!$showRoute && $this->source == ProductSource::LOCAL, fn() => $this->in_stock),
            'marked_as_out_of_stock' => $this->when($showRoute && !$this->has_variants && $this->source == ProductSource::LOCAL, $this->marked_as_out_of_stock),
            'quantity' => $this->when($showStock, fn() => $this->total_stock),
            'image' => $this->when($showRoute, fn() => $this->image?->getUrl()),
            'new_api_image_available' => $this->when($showRoute && $this->source == ProductSource::API, fn() => $this->newAvailableImage?->image?->getUrl()),
            'is_viewed' => $this->when($this->source == ProductSource::API, $this->viewed_by_admin),
            'created_at' => formatDateTime($this->created_at),
            'unused_codes' => $this->when($showCodes, fn() => CodeResource::collection($this->validCodes)),
            'expired_codes' => $this->when($showCodes, fn() => CodeResource::collection($this->expiredCodes)),
        ];
    }

    public function indexMarkupFee()
    {
        return [
            'origin' => $this->has_custom_markup_fee ? 'custom' : 'global',
            'type' => $this->has_custom_markup_fee ? $this->custom_markup_fee_type : getSetting('markup_fee', 'markup_fee_type'),
            'value' => $this->has_custom_markup_fee ? formatMoney($this->custom_markup_fee_value) : formatMoney(getSetting('markup_fee', 'markup_fee_value'))
        ];
    }
}
