<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Models\Role;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Admin extends Authenticatable implements HasMedia
{
    use HasApiTokens, HasRoles, Notifiable, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'name',
        'email',
        'country_code',
        'phone',
        'password',
        'is_active',
        'role_id',
        'app_locale',
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

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function getImageAttribute()
    {
        return $this->getMedia('*')->first();
    }

    public function fcmTokens()
    {
        return $this->morphMany(FcmToken::class, 'model');
    }
}
