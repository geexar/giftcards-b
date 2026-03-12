<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class User extends Authenticatable implements HasMedia
{
    use HasApiTokens, Notifiable, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'name',
        'email',
        'country_code',
        'phone',
        'password',
        'is_active',
        'balance'
    ];

    protected $hidden = [
        'password'
    ];


    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function getEmailAttribute($value)
    {
        return $this->deleted_at ? restoreInvalidatedValue($value) : $value;
    }

    public function socialProviders()
    {
        return $this->hasMany(UserSocialProvider::class);
    }

    public function getImageAttribute()
    {
        return $this->getMedia('*')->first();
    }
}
