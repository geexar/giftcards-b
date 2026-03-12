<?php

namespace App\Repositories;

use App\Models\Admin;

class AdminRepository extends BaseRepository
{
    public function __construct(Admin $model)
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

    public function getPaginatedAdmins()
    {
        return $this->model
            ->when(request('search'), function ($query, $name) {
                $query->where(function ($q) use ($name) {
                    $q->where('name', 'like', "%{$name}%")
                        ->orWhere('email', 'like', "%{$name}%");
                });
            })
            ->when(request()->has('is_active'), fn($query) => $query->where('is_active', request('is_active')))
            ->with('media')
            ->latest()
            ->paginate(page: request('page'), perPage: request('per_page'));
    }

    public function invalidateAdminData(Admin $admin)
    {
        $admin->update([
            'email' => getInvalidatedValue($admin->email)
        ]);
    }

    public function getNotifiedAdmins(string $permission)
    {
        return $this->model->whereHas('permissions', function ($query) use ($permission) {
            $query->where('name', $permission);
        })->get();
    }

    public function adminCountByRole(string $id)
    {
        return $this->model->where('role_id', $id)->count();
    }
}
