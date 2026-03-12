<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'email',
        'country_code',
        'phone',
        'message',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
