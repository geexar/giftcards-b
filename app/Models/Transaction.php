<?php

namespace App\Models;

use App\Enums\ActorType;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Transaction extends Model
{
    use HasTranslations;

    protected $fillable = [
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
        'affects_wallet',
    ];

    public array $translatable = [
        'description',
    ];

    protected function casts(): array
    {
        return [
            'type' => TransactionType::class,
            'actor_type' => ActorType::class,
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

    public function actor()
    {
        if ($this->actor_type == ActorType::USER) {
            return $this->belongsTo(User::class);
        } elseif ($this->actor_type == ActorType::ADMIN) {
            return $this->belongsTo(Admin::class);
        }

        return null;
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
