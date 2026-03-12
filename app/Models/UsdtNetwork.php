<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsdtNetwork extends Model
{
    protected $fillable = [
        'identifier',
        'name',
        'fixed_fees',
        'percentage_fees',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean'
        ];
    }
}