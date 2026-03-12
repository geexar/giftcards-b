<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'type',
        'notifiable_type',
        'notifiable_id',
        'data',
        'read_at'
    ];

    protected $casts = [
        'data' => 'array'
    ];


    public function getTitleAttribute()
    {
        if (isset($this->data['title']['ar']) || isset($this->data['title']['en'])) {
            return $this->data['title'][app()->getLocale()];
        }

        return $this->data['title'];
    }

    public function getBodyAttribute()
    {
        if (isset($this->data['body']['ar']) || isset($this->data['body']['en'])) {
            return $this->data['body'][app()->getLocale()];
        }

        return $this->data['body'];
    }
}
