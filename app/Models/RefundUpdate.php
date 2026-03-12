<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class RefundUpdate extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'actor_type',
        'actor_id',
        'refund_id',
        'old_status',
        'new_status',
        'amount',
        'created_at',
    ];

    public function actor(): MorphTo
    {
        return $this->morphTo('actor', 'actor_type', 'actor_id')->withTrashed();
    }
}
