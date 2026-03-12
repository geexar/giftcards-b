<?php

namespace App\Models;

use App\Enums\DeliveryType;
use App\Enums\MarkupFeeOrigin;
use App\Enums\OrderItemStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class OrderItem extends Model
{
    protected $fillable = [
        'item_no',
        'order_id',
        'product_id',
        'product_variant_value_id',
        'delivery_type',
        'provider_original_price',
        'price',
        'markup_fee_origin',
        'markup_fee_type',
        'markup_fee_value',
        'user_facing_price',
        'quantity',
        'total',
        'fulfilled_quantity',
        'status',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderItemStatus::class,
            'markup_fee_origin' => MarkupFeeOrigin::class,
            'delivery_type' => DeliveryType::class,
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function variantValue()
    {
        return $this->belongsTo(ProductVariantValue::class, 'product_variant_value_id')->withTrashed();
    }

    public function codes(): HasManyThrough
    {
        return $this->hasManyThrough(Code::class, OrderItemCode::class, 'order_item_id', 'id', 'id', 'code_id');
    }

    public function getItemAttribute()
    {
        return $this->variantValue ?? $this->product;
    }

    public function rating()
    {
        return $this->hasOne(Rating::class);
    }

    public function statusUpdateLogs()
    {
        return $this->morphMany(StatusUpdateLog::class, 'model')->latest();
    }

    public function getCanCancelAttribute()
    {
        return $this->status == OrderItemStatus::PENDING_CONFIRMATION;
    }
}
