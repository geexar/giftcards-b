<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\RefundStatus;
use App\Traits\DateRangeFilter;
use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    use DateRangeFilter;

    protected $fillable = [
        'refund_no',
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

    public function transaction()
    {
        return $this->hasOne(Transaction::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function processor()
    {
        return $this->belongsTo(Admin::class, 'processed_by')->withTrashed();
    }

    public function updates()
    {
        return $this->hasMany(RefundUpdate::class);
    }

    public function getCanMakeProcessedAttribute()
    {
        $finalState = [OrderStatus::COMPLETED, OrderStatus::PROCESSED, OrderStatus::REJECTED, OrderStatus::FAILED, OrderStatus::CANCELED];

        return in_array($this->order->status, $finalState);
    }
}
