<?php

namespace App\Models;

use App\Enums\OrderItemStatus;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'itemable_type',
        'itemable_id',
        'price',
        'quantity',
        'total',
        'fulfilled_quantity',
        'fulfilled_total',
        'displayed_price',
        'status',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderItemStatus::class,
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function itemable()
    {
        return $this->morphTo();
    }

    public function codes()
    {
        return $this->hasMany(OrderItemCode::class);
    }
}
