<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Code extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'codable_type',
        'codable_id',
        'code',
        'pin_code',
        'info_1',
        'info_2',
        'expiry_date',
        'is_used',
    ];

    protected function casts(): array
    {
        return [
            'is_used' => 'boolean'
        ];
    }

    public function codable()
    {
        return $this->morphTo();
    }
}
