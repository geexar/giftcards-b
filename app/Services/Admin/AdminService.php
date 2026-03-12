<?php

namespace App\Services\Admin;

use App\Mail\AdminAccountCreatedMail;
use App\Repositories\AdminRepository;
use App\Repositories\PermissionRepository;
use App\Repositories\RoleRepository;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AdminService
{
    public function __construct(
        private AdminRepository $adminRepository,
        private PermissionRepository $permissionRepository,
        private ActivityLogService $activityLogService
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

        $password = $data['password'];
        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        $admin = DB::transaction(function () use ($data) {
            $admin = $this->adminRepository->create($data);

            if (isset($data['image'])) {
                $admin->addMedia($data['image'])->toMediaCollection();
            }

            $permissions = $this->permissionRepository->getByIds($data['permissions']);

            $admin->givePermissionTo($permissions);

            $this->activityLogService->store($admin, 'admin.created');

            return $admin;
        });

        Mail::to($admin->email)->send(new AdminAccountCreatedMail($admin->refresh(), $password));
    }

    public function update(string $id, array $data)
    {
        $admin = $this->getAdmin($id);

        if ($admin->id == 1 || $admin->id == auth('admin')->id()) {
            throw new BadRequestHttpException('You can not update this admin');
        }

        if (isset($data['phone'])) {
            $data['phone'] = normalizePhoneNumber($data['phone']);
        }

        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        DB::transaction(function () use ($admin, $data) {
            $this->adminRepository->update($admin, $data);

            if (isset($data['image'])) {
                $admin->clearMediaCollection();
                $admin->addMedia($data['image'])->toMediaCollection();
            }

            if (!$data['is_active']) {
                $admin->tokens()->delete();
            }

            $permissions = $this->permissionRepository->getByIds($data['permissions']);

            // check if admins permissions has be changed to log him out    
            $diff1 = array_diff($admin->permissions->pluck('id')->toArray(), $data['permissions']);
            $diff2 = array_diff($data['permissions'], $admin->permissions->pluck('id')->toArray());
            $permissionDifferences = count(array_merge($diff1, $diff2));

            if ($permissionDifferences > 0) {
                $admin->tokens()->delete();
            }

            $admin->syncPermissions($permissions);

            $this->activityLogService->store($admin, 'admin.updated');
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

            $this->activityLogService->store($admin, 'admin.deleted');
        });
    }

    public function toggleStatus(string $id)
    {
        $admin = $this->getAdmin($id);

        if ($admin->id == 1 || $admin->id == auth('admin')->id()) {
            throw new BadRequestHttpException('You can not toggle status for this admin');
        }

        DB::transaction(function () use ($admin) {
            $newStatus = !$admin->is_active;

            $admin->update(['is_active' => $newStatus]);

            if ($newStatus == false) {
                $admin->tokens()->delete();
            }

            $this->activityLogService->store($admin, 'admin.updated');
        });
    }
}
