<?php

namespace App\Models;

use App\Enums\DeliveryType;
use App\Enums\ProductStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Product extends Model
{
    use SoftDeletes, HasTranslations;

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
        'has_custom_markup_fees',
        'custom_markup_fees_type',
        'custom_markup_fees',
        'has_variants',
        'base_price',
        'has_discount',
        'discount_type',
        'discount',
        'final_price',
        'delivery_type',
        'marked_as_out_of_stock',
        'quantity',
        'informational_quantity',
        'viewed_by_admin'
    ];

    public array $translatable = [
        'name',
        'short_description',
        'description'
    ];


    protected function casts(): array
    {
        return [
            'status' => ProductStatus::class,
            'delivery_type' => DeliveryType::class,
            'is_global' => 'boolean',
            'marked_as_out_of_stock' => 'boolean',
            'viewed_by_admin' => 'boolean',
            'has_variants' => 'boolean',
            'has_custom_markup_fees' => 'boolean',
            'has_discount' => 'boolean',
        ];
    }


    public function getImageAttribute()
    {
        return $this->getMedia('*')->first();
    }

    public function countries()
    {
        return $this->belongsToMany(Country::class, 'product_country', 'product_id', 'country_id');
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable', 'model_tag');
    }

    public function variant()
    {
        return $this->hasOne(ProductVariant::class);
    }
}
