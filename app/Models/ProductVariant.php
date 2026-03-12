<?php

namespace App\Models;

use App\Enums\DeliveryType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'product_id',
        'name',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function values()
    {
        return $this->hasMany(ProductVariantValue::class);
    }

    public function visibleValues()
    {
        return $this->values()->where('is_visible', true);
    }

    public function firstVisibleValue()
    {
        return $this->hasOne(ProductVariantValue::class)->where('is_visible', true);
    }

    public function requiresConfirmationValues()
    {
        return $this->values()->where('delivery_type', DeliveryType::REQUIRES_CONFIRMATION->value);
    }

      public function instantValues()
    {
        return $this->values()->where('delivery_type', DeliveryType::INSTANT->value);
    }
}
