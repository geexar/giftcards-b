<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Integration extends Model
{
    protected $fillable = [
        'name',
        'config'
    ];

    protected $casts = [
        'config' => 'array',
    ];

    public function credentials(): MorphMany
    {
        return $this->morphMany(Credential::class, 'owner');
    }
}
