<?php

namespace App\Models;

use App\Enums\DeliveryType;
use App\Observers\ProductVariantValueObserver;
use App\Services\Admin\MarkupFeeService;
use App\Services\User\ProductStockService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class ProductVariantValue extends Model
{
    use SoftDeletes, HasTranslations;

    protected $fillable = [
        'product_variant_id',
        'value',
        'description',
        'is_visible',
        'base_price',
        'has_discount',
        'discount_type',
        'discount_value',
        'final_price',
        'delivery_type',
        'marked_as_out_of_stock',
        'manual_stock',
        'reserved_stock',
        'avg_rating'
    ];

    public $translatable = [
        'description'
    ];

    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
            'has_discount' => 'boolean',
            'marked_as_out_of_stock' => 'boolean',
            'delivery_type' => DeliveryType::class,
        ];
    }

    public function getValueAttribute($value)
    {
        return $this->deleted_at ? restoreInvalidatedValue($value) : $value;
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function getProductAttribute()
    {
        return $this->variant->product;
    }

    public function getUserFacingPriceAttribute()
    {
        return $this->final_price + $this->markup_fee;
    }

    public function getMarkupFeeAttribute()
    {
        return app(MarkupFeeService::class)->calculateMarkupFee($this->product, $this, 'base_price');
    }

    public function getInStockAttribute(): bool
    {
        return app(ProductStockService::class)->getVariantValueStock($this) > 0;
    }

    public function getStockAttribute()
    {
        return app(ProductStockService::class)->getVariantValueStock($this);
    }

    public function getAvailableStockAttribute(): int
    {
        return app(ProductStockService::class)->getVariantValueAvailableStock($this);
    }

    public function getHasAvailableStockAttribute(): bool
    {
        return $this->available_stock > 0;
    }

    public function getPriceBeforeDiscountAttribute()
    {
        if (!$this->has_discount) {
            return $this->user_facing_price;
        }

        if ($this->discount_type == 'percentage') {
            return $this->discount_value == 100 ?
                $this->base_price + $this->markup_fee :
                $this->user_facing_price / (1 - $this->discount_value / 100);
        }

        return $this->user_facing_price + $this->discount_value;
    }

    public function codes()
    {
        return $this->morphMany(Code::class, 'codeable');
    }

    public function validCodes()
    {
        return $this->codes()
            ->where('is_used', false)
            ->where(fn($q) => $q->whereNull('expiry_date')->orWhereDate('expiry_date', '>', now()->toDateString()));
    }

    public function purchasableCodes()
    {
        return $this->validCodes()->whereNull('reserved_at');
    }

    public function usedCodes()
    {
        return $this->codes()->where('is_used', true);
    }

    public function expiredCodes()
    {
        return $this->codes()->whereDate('expiry_date', '<=', now()->toDateString());
    }

    public function availabilitySubscriptions()
    {
        return $this->hasMany(ProductAvailabilitySubscription::class, 'product_variant_value_id');
    }
}
