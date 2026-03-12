<?php

namespace App\Models;

use App\Enums\DeliveryType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariantValue extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'product_variant_id',
        'description_en',
        'description_ar',
        'is_visible',
        'value',
        'base_price',
        'has_discount',
        'discount_type',
        'discount',
        'final_price',
        'delivery_type',
        'marked_as_out_of_stock',
        'quantity',
        'informational_quantity',
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

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }
}
