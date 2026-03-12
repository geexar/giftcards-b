<?php

namespace App\Repositories;

use App\Models\FcmToken;
use Illuminate\Database\Eloquent\Model;

class FcmTokenRepository
{
    public function __construct(private FcmToken $model) {}

    public function createOrUpdate(Model $model, string $device_id, string $fcm_token, $auth_token_id = null): FcmToken
    {
        return $this->model->updateOrCreate(
            [
                'model_id' => $model->id,
                'model_type' => get_class($model),
                'device_id' => $device_id,
            ],
            [
                'token' => $fcm_token,
                'auth_token_id' => $auth_token_id,
            ]
        );
    }
}
