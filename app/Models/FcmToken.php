<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FcmToken extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'model_type',
        'model_id',
        'auth_token_id',
        'device_id',
        'token'
    ];
}