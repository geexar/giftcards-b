<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSocialProvider extends Model
{
    protected $fillable = [
        'user_id',
        'provider',
        'provider_id',
        'apple_relay_email',
    ];

    /**
     * The user that owns this social provider.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
