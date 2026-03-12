<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\Translatable\HasTranslations;

class ActivityLog extends Model
{
    use HasTranslations;

    public $timestamps = false;

    protected $fillable = [
        'actor_type',
        'actor_id',
        'model_type',
        'model_id',
        'description',
        'ip_address',
        'created_at'
    ];

    public array $translatable = ['description'];

    /**
     * The actor who performed the action (polymorphic)
     */
    public function actor(): MorphTo
    {
        return $this->morphTo('actor', 'actor_type', 'actor_id')->withTrashed();
    }

    /**
     * The model that was acted upon
     */
    public function operatedOn(): MorphTo
    {
        return $this->morphTo(null, 'model_type', 'model_id')->withTrashed();
    }
}
