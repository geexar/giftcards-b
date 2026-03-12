<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Models\Role;

class Admin extends Authenticatable
{
    use HasApiTokens, HasRoles, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'country_code',
        'phone',
        'password',
        'is_active',
        'role_id'
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
}
