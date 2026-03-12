<?php

namespace App\Services\Admin;

use App\Repositories\AdminRepository;
use App\Repositories\PermissionRepository;
use App\Repositories\RoleRepository;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AdminService
{
    public function __construct(
        private AdminRepository $adminRepository,
        private PermissionRepository $permissionRepository
    ) {}

    public function getAdmin(string $id)
    {
        $admin = $this->adminRepository->getById($id);

        if (!$admin) {
            throw new NotFoundHttpException('Admin not found');
        }

        return $admin;
    }

    public function create(array $data)
    {
        if (isset($data['phone'])) {
            $data['phone'] = normalizePhoneNumber($data['phone']);
        }

        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        DB::transaction(function () use ($data) {
            $admin = $this->adminRepository->create($data);

            if (isset($data['image'])) {
                $admin->addMedia($data['image'])->toMediaCollection();
            }

            $permissions = $this->permissionRepository->getByIds($data['permissions']);

            $admin->givePermissionTo($permissions);
        });
    }

    public function update(string $id, array $data)
    {
        $admin = $this->getAdmin($id);

        if (isset($data['phone'])) {
            $data['phone'] = normalizePhoneNumber($data['phone']);
        }

        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        DB::transaction(function () use ($admin, $data) {
            $this->adminRepository->update($admin, $data);

            if (isset($data['image'])) {
                $admin->addMedia($data['image'])->toMediaCollection();
            }

            if (isset($data['is_active']) && !$data['is_active']) {
                $admin->tokens()->delete();
            }

            $permissions = $this->permissionRepository->getByIds($data['permissions']);

            $admin->givePermissionTo($permissions);
        });
    }

    public function delete(string $id)
    {
        $admin = $this->getAdmin($id);

        if ($admin->id == 1 || $admin->id == auth('admin')->id()) {
            throw new BadRequestHttpException('You can not delete this admin');
        }

        DB::transaction(function () use ($admin) {
            $admin->delete();
            $this->adminRepository->invalidateAdminData($admin);
        });
    }
}
