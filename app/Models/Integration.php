<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Integration extends Model
{
    protected $fillable = [
        'name',
        'code',
        'config',
        'active_mode',
    ];

    protected $casts = [
        'config' => 'array',
    ];

    public function credentials(): MorphMany
    {
        return $this->morphMany(Credential::class, 'owner');
    }

    public function sandboxCredentials(): MorphMany
    {
        return $this->morphOne(Credential::class, 'owner')->where('mode', 'sandbox');
    }

    public function liveCredentials(): MorphMany
    {
        return $this->morphOne(Credential::class, 'owner')->where('mode', 'live');
    }

    public function activeCredentials(): MorphOne
    {
        return $this->morphOne(Credential::class, 'owner')->where('mode', $this->active_mode);
    }
}
