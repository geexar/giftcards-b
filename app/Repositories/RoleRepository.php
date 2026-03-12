<?php

namespace App\Repositories;

use Spatie\Permission\Models\Role;

class RoleRepository extends BaseRepository
{
    public function __construct(Role $model)
    {
        parent::__construct($model);
    }

    public function getPaginatedRoles()
    {
        return $this->model
            ->when(request('search'), function ($query, $name) {
                return $query->where('name', 'like', "%{$name}%");
            })
            ->when(request()->has('is_active'), fn($query) => $query->where('is_active', request('is_active')))
            ->paginate(page: request('page'), perPage: request('per_page'));
    }

    public function invalidateRoleData(Role $role)
    {
        $role->update([
            'name' => getInvalidatedValue($role->name)
        ]);
    }

    public function getDdl(?bool $with_permissions = false)
    {
        return $this->model
            ->where('is_active', true)
            ->when($with_permissions, fn($query) => $query->with('permissions'))
            ->get();
    }
}
