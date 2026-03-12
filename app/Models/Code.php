<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Code extends Model
{
    protected $fillable = [
        'codeable',
        'codeable',
        'code',
        'code_hash',
        'pin_code',
        'info_1',
        'info_2',
        'expiry_date',
        'is_used',
        'reserved_at',
        'reference_id',
        'raw_response'
    ];

    protected function casts(): array
    {
        return [
            'is_used' => 'boolean'
        ];
    }

    public function codeable()
    {
        return $this->morphTo();
    }

    // Encrypt code on set
    public function setCodeAttribute($value)
    {
        $this->attributes['code'] = Crypt::encryptString($value);
        $this->attributes['code_hash'] = hash('sha256', $value);
    }

    public function setPinCodeAttribute($value)
    {
        $this->attributes['pin_code'] = Crypt::encryptString($value);
    }

    public function setRawResponseAttribute($value)
    {
        $this->attributes['raw_response'] = Crypt::encryptString($value);
    }

    // Decrypt code on get
    public function getCodeAttribute($value)
    {
        $value = Crypt::decryptString($value);

        return $this->deleted_at ? restoreInvalidatedValue($value) : $value;
    }

    // Decrypt response on get
    public function getRawResponseAttribute($value)
    {
        if ($value === null) {
            return null;
        }

        return Crypt::decryptString($value);
    }

    public function getPinCodeAttribute($value)
    {
        if ($value === null) {
            return null;
        }

        return Crypt::decryptString($value);
    }
}
