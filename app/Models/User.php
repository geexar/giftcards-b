<?php

namespace App\Models;

use App\Traits\DateRangeFilter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class User extends Authenticatable implements HasMedia
{
    use HasApiTokens, Notifiable, SoftDeletes, InteractsWithMedia, DateRangeFilter;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'uuid',
        'name',
        'email',
        'country_code',
        'phone',
        'password',
        'is_active',
        'balance',
        'app_locale'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var string[]
     */
    protected $hidden = [
        'password',
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

    public function getAppleProviderAttribute()
    {
        return $this->socialProviders->where('provider', 'apple')->first();
    }

    public function getImageAttribute()
    {
        return $this->getMedia('*')->first();
    }

    public function cart()
    {
        return $this->hasOne(Cart::class);
    }

    public function fcmTokens()
    {
        return $this->morphMany(FcmToken::class, 'model');
    }

    public function orders()
    {
        return $this->hasMany(Order::class)->excludeWaitingPayment();
    }

    public function latestOrder()
    {
        return $this->hasOne(Order::class)->excludeWaitingPayment()->latest();
    }
}
