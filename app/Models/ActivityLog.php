<?php

namespace App\Models;

use App\Enums\ActivityLogType;
use App\Enums\ActorType;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class ActivityLog extends Model
{
    use HasTranslations;

    protected $fillable = [
        'actor_type',
        'actor_id',
        'description',
    ];

    public array $translatable = [
        'description',
    ];

    protected function casts(): array
    {
        return [
            'type' => ActorType::class,
        ];
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
}
