<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupedNotification extends Model
{
    protected $fillable = [
        'title',
        'body',
        'sent_to_all',
        'sent_count',
    ];

    protected function casts(): array
    {
        return [
            'sent_to_all' => 'boolean',
        ];
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'grouped_notification_user', 'grouped_notification_id', 'user_id');
    }
}
