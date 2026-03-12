<?php

namespace App\Repositories;

use App\Models\UserSocialProvider;

class UserSocialProviderRepository
{
    public function __construct(private UserSocialProvider $model) {}
}
