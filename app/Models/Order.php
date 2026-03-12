<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_no',
        'user_id',
        'payment_method_id',
        'name',
        'email',
        'status',
        'total',
        'is_gifted',
        'gifted_email',
        'transaction_id',
        'processed_by',
        'rating',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'is_gifted' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function processor()
    {
        return $this->belongsTo(Admin::class, 'processed_by');
    }
}
