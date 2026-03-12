<?php

namespace App\Services\Admin;

use App\Repositories\PermissionRepository;
use App\Repositories\RoleRepository;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RoleService
{
    public function __construct(
        private RoleRepository $roleRepository,
        private PermissionRepository $permissionRepository
    ) {}

    public function getRole(string $id)
    {
        $role = $this->roleRepository->getById($id);

        if (!$role) {
            throw new NotFoundHttpException('role not found');
        }

        return $role;
    }

    public function create(array $data)
    {
        DB::transaction(function () use ($data) {
            $role = $this->roleRepository->create([
                'name' => $data['name'],
                'guard_name' => 'admin',
            ]);

            $permissions = $this->permissionRepository->getByIds($data['permissions']);

            $role->syncPermissions($permissions);
        });
    }

    public function update(string $id, array $data)
    {
        $role = $this->getRole($id);

        if ($role->id == 1) {
            throw new BadRequestHttpException('cannot update this role');
        }

        DB::transaction(function () use ($role, $data) {
            $this->roleRepository->update($role, [
                'name' => $data['name'],
                'is_active' => $data['is_active'],
            ]);

            $permissions = $this->permissionRepository->getByIds($data['permissions']);

            $role->syncPermissions($permissions);
        });
    }

    public function delete(string $id)
    {
        $role = $this->getRole($id);

        if ($role->id == 1) {
            throw new BadRequestHttpException('cannot delete this role');
        }

        $role->delete();
    }
}
