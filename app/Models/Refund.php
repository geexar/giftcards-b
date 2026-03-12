<?php

namespace App\Models;

use App\Enums\RefundStatus;
use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    protected $fillable = [
        'order_id',
        'amount',
        'status',
        'processed_by',
        'processed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => RefundStatus::class
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function processor()
    {
        return $this->belongsTo(Admin::class, 'processed_by');
    }
}
