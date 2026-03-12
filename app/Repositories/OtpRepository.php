<?php

namespace App\Repositories;

use App\Enums\OtpType;
use App\Models\Otp;

class OtpRepository extends BaseRepository
{
    public function __construct(Otp $model)
    {
        parent::__construct($model);
    }

    public function getLatest(string $user_type, string $email, OtpType $type)
    {
        return $this->model
            ->where('user_type', $user_type)
            ->where('email', $email)
            ->where('type', $type->value)
            ->orderBy('id', 'desc')
            ->first();
    }

    public function deleteAll(string $user_type, string $email, OtpType $type)
    {
        return $this->model
            ->where('user_type', $user_type)
            ->where('email', $email)
            ->where('type', $type->value)
            ->delete();
    }
}
