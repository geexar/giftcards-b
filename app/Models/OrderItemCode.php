<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItemCode extends Model
{
    protected $fillable = [
        'order_item_id',
        'code_id',
    ];

    public $timestamps = false;

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function code()
    {
        return $this->belongsTo(Code::class);
    }
}
