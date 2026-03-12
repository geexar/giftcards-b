<?php

namespace App\Models;

use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Enums\TransactionType;
use App\Observers\OrderObserver;
use App\Traits\DateRangeFilter;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use DateRangeFilter;

    protected $fillable = [
        'item_no',
        'order_no',
        'user_id',
        'payment_method_id',
        'name',
        'email',
        'status',
        'total',
        'is_gifted',
        'gifted_email',
        'processed_by',
        'processed_at',
        'notes'
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'is_gifted' => 'boolean',
        ];
    }

    public function scopeExcludeWaitingPayment($query)
    {
        return $query->whereNotIn('status', [OrderStatus::WAITING_PAYMENT->value, OrderStatus::EXPIRED->value]);
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function processor()
    {
        return $this->belongsTo(Admin::class, 'processed_by')->withTrashed();
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class)->where('type', TransactionType::PURCHASE->value);
    }

    public function refund()
    {
        return $this->hasOne(Refund::class);
    }

    public function activityLogs()
    {
        return $this->morphMany(ActivityLog::class, 'model')->latest();
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function itemRatings()
    {
        return $this->hasMany(Rating::class)->whereNotNull('order_item_id');
    }

    public function overallRating()
    {
        return $this->hasOne(Rating::class)->whereNull('order_item_id');
    }

    public function statusUpdateLogs()
    {
        return $this->morphMany(StatusUpdateLog::class, 'model')->latest();
    }

    public function notesUpdates()
    {
        return $this->hasMany(OrderNotesUpdate::class, 'order_id');
    }

    public function getNetAmountAttribute()
    {
        return $this->total - ($this->refund?->amount ?? 0);
    }

    public function getCanCancelAttribute()
    {
        return $this->status == OrderStatus::PENDING_CONFIRMATION;
    }

    public function getCanRateAttribute()
    {
        return in_array($this->status, [OrderStatus::PROCESSED, OrderStatus::COMPLETED, OrderStatus::PARTIALLY_COMPLETED]) && !$this->overallRating;
    }
}
