<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Credential extends Model
{
    protected $fillable = [
        'owner_type',
        'owner_id',
        'data',
        'mode'
    ];


    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    // Automatically encrypt JSON before saving
    public function setDataAttribute(array|string $value): void
    {
        if (is_array($value)) {
            $value = json_encode($value);
        }

        $this->attributes['data'] = Crypt::encryptString($value);
    }

    // Automatically decrypt JSON when accessed
    public function getDataAttribute(string $value): array
    {
        return json_decode(Crypt::decryptString($value), true);
    }
}
