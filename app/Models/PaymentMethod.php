<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class PaymentMethod extends Model
{
    protected $fillable = [
        'name',
        'code',
        'config',
        'is_active',
        'active_for_checkout',
        'active_for_top_up',
        'active_mode'
    ];


    protected function casts(): array
    {
        return [
            'config' => 'array',
            'is_active' => 'boolean',
            'active_for_checkout' => 'boolean',
            'active_for_top_up' => 'boolean',
        ];
    }

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
