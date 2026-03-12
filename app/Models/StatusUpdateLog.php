<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StatusUpdateLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'actor_type',
        'actor_id',
        'model_type',
        'model_id',
        'old_status',
        'new_status',
        'created_at',
    ];

    /**
     * Who performed the status change
     */
    public function actor(): MorphTo
    {
        return $this->morphTo('actor', 'actor_type', 'actor_id')->withTrashed();
    }

    /**
     * Model whose status was changed
     */
    public function model(): MorphTo
    {
        return $this->morphTo('model', 'model_type', 'model_id')->withTrashed();
    }
}
