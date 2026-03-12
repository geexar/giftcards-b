<?php

namespace App\Models;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Traits\DateRangeFilter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Transaction extends Model
{
    use DateRangeFilter;

    protected $fillable = [
        'transaction_no',
        'type',
        'user_id',
        'actor_type',
        'actor_id',
        'amount',
        'status',
        'reference_id',
        'order_id',
        'refund_id',
        'projected_profit',
        'actual_profit',
        'description',
        'payment_method_id',
        'usdt_network',
        'affects_wallet',
    ];

    protected function casts(): array
    {
        return [
            'type' => TransactionType::class,
            'status' => TransactionStatus::class,
            'affects_wallet' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function actor(): MorphTo
    {
        return $this->morphTo('actor', 'actor_type', 'actor_id');
    }

    public function refund()
    {
        return $this->belongsTo(Refund::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }
}
