<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository extends BaseRepository
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function getByPhone(string $country_code, string $phone)
    {
        return $this->model->where('country_code', $country_code)->where('phone', $phone)->first();
    }

    public function getByEmail($email)
    {
        return $this->model->where('email', $email)->first();
    }

    public function getPaginatedUsers()
    {
        return $this->model
            ->when(request('search'), function ($query, $name) {
                return $query->where('name', 'like', "%{$name}%")
                    ->orWhere('email', 'like', "%{$name}%");
            })
            ->when(request()->has('is_active'), fn($query) => $query->where('is_active', request('is_active')))
            ->paginate(page: request('page'), perPage: request('per_page'));
    }

    public function invalidateUserData(User $user)
    {
        $user->update([
            'email' => getInvalidatedValue($user->email)
        ]);
    }
}
