<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookLog extends Model
{
    protected $fillable = [
        'provider',
        'payload',
        'signature_valid',
        'status_code',
        'message',
    ];


    protected function casts(): array
    {
        return [
            'signature_valid' => 'boolean',
        ];
    }
}
