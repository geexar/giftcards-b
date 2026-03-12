<?php

namespace App\Models;

use App\Enums\DeliveryType;
use App\Enums\ProductNewImageStatus;
use App\Enums\ProductSource;
use App\Enums\ProductStatus;
use App\Observers\ProductObserver;
use App\Services\Admin\MarkupFeeService;
use App\Services\User\ProductStockService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements HasMedia
{
    use SoftDeletes, HasTranslations, InteractsWithMedia;

    protected $fillable = [
        'source',
        'external_id',
        'sku',
        'name',
        'short_description',
        'description',
        'category_id',
        'status',
        'is_global',
        'has_custom_markup_fee',
        'custom_markup_fee_type',
        'custom_markup_fee_value',
        'has_variants',
        'provider_original_price',
        'base_price',
        'has_discount',
        'discount_type',
        'discount_value',
        'final_price',
        'delivery_type',
        'marked_as_out_of_stock',
        'manual_stock',
        'reserved_stock',
        'viewed_by_admin',
        'api_stock_available',
        'api_stock_last_checked_at',
        'is_best_seller',
        'is_popular',
        'is_featured',
        'is_trending',
        'avg_rating'
    ];

    public array $translatable = [
        'name',
        'short_description',
        'description'
    ];


    protected function casts(): array
    {
        return [
            'source' => ProductSource::class,
            'status' => ProductStatus::class,
            'delivery_type' => DeliveryType::class,
            'is_global' => 'boolean',
            'marked_as_out_of_stock' => 'boolean',
            'viewed_by_admin' => 'boolean',
            'api_stock_available' => 'boolean',
            'has_variants' => 'boolean',
            'has_custom_markup_fee' => 'boolean',
            'has_discount' => 'boolean',
            'is_best_seller' => 'boolean',
            'is_popular' => 'boolean',
            'is_featured' => 'boolean',
            'is_trending' => 'boolean',
        ];
    }

    public static function booted()
    {
        static::observe(ProductObserver::class);
    }

    public function getImageAttribute()
    {
        return $this->getMedia('*')->first();
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function countries()
    {
        return $this->belongsToMany(Country::class, 'product_country', 'product_id', 'country_id');
    }

    public function variant()
    {
        return $this->hasOne(ProductVariant::class);
    }

    public function getUserFacingPriceAttribute()
    {
        return $this->final_price + $this->markup_fee;
    }

    public function getMarkupFeeAttribute()
    {
        return app(MarkupFeeService::class)->calculateMarkupFee($this, null, 'base_price');
    }

    public function getInStockAttribute(): bool
    {
        if ($this->source == ProductSource::API) {
            return $this->api_stock_available;
        }

        return app(ProductStockService::class)->getLocalProductTotalStock($this) > 0;
    }

    public function getTotalStockAttribute(): int
    {
        if ($this->source == ProductSource::API) {
            return 0;
        }

        return app(ProductStockService::class)->getLocalProductTotalStock($this);
    }

    public function getAvailableStockAttribute(): int
    {
        return app(ProductStockService::class)->getLocalProductAvailableStock($this);
    }

    public function getHasAvailableStockAttribute()
    {
        if ($this->source == ProductSource::API) {
            return $this->api_stock_available;
        }

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

    public function syncLogs()
    {
        return $this->hasMany(ProductSyncLogItem::class, 'product_id');
    }

    public function newAvailableImage()
    {
        return $this->hasOne(ProductSyncLogItem::class, 'product_id')
            ->latest()
            ->where('new_image_available', true)
            ->where('new_image_status', ProductNewImageStatus::PENDING);
    }

    public function scopeWithActiveCategory($query)
    {
        return $query->whereHas('category', function ($q) {
            $q->where('is_active', true)
                ->where(function ($q2) {
                    // Either parent does not exist OR parent is active
                    $q2->whereDoesntHave('parent')
                        ->orWhereHas('parent', function ($q3) {
                            $q3->where('is_active', true)
                                ->where(function ($q4) {
                                    // Either grandparent does not exist OR grandparent is active
                                    $q4->whereDoesntHave('parent')
                                        ->orWhereHas('parent', function ($q5) {
                                            $q5->where('is_active', true);
                                        });
                                });
                        });
                });
        });
    }

    public function availabilitySubscriptions()
    {
        return $this->hasMany(ProductAvailabilitySubscription::class, 'product_id')->whereNull('product_variant_value_id');
    }
}
