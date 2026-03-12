<?php

namespace App\Services\Admin;

use App\Repositories\AdminRepository;
use App\Repositories\PermissionRepository;
use App\Repositories\RoleRepository;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RoleService
{
    public function __construct(
        private RoleRepository $roleRepository,
        private PermissionRepository $permissionRepository,
        private ActivityLogService $activityLogService,
        private AdminRepository $adminRepository
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

            $this->activityLogService->store($role, 'role.created');
        });
    }

    public function update(string $id, array $data)
    {
        $role = $this->getRole($id);

        if ($role->id == 1) {
            throw new BadRequestHttpException('cannot update this role');
        }

        $attachedToAdmins = (bool) $this->adminRepository->adminCountByRole($id);

        if ($role->is_active && !$data['is_active'] && $attachedToAdmins) {
            throw new BadRequestHttpException(__("can't deactive this role because it is assigned to admins"));
        }

        DB::transaction(function () use ($role, $data) {
            $this->roleRepository->update($role, [
                'name' => $data['name'],
                'is_active' => $data['is_active'],
            ]);

            $permissions = $this->permissionRepository->getByIds($data['permissions']);

            $role->syncPermissions($permissions);

            $this->activityLogService->store($role, 'role.updated');
        });
    }

    public function delete(string $id)
    {
        $role = $this->getRole($id);

        if ($role->id == 1) {
            throw new BadRequestHttpException('cannot delete this role');
        }

        $attachedToAdmins = (bool) $this->adminRepository->adminCountByRole($id);

        if ($attachedToAdmins) {
            throw new BadRequestHttpException(__("can't delete this role because it is assigned to admins"));
        }

        DB::transaction(function () use ($role) {
            $role->delete();
            $this->activityLogService->store($role, 'role.deleted');
        });
    }

    public function toggleStatus(string $id)
    {
        $role = $this->getRole($id);

        if ($role->id == 1) {
            throw new BadRequestHttpException('cannot update this role');
        }

        DB::transaction(function () use ($role) {
            $role->update(['is_active' => !$role->is_active]);
            $this->activityLogService->store($role, 'role.updated');
        });
    }
}
