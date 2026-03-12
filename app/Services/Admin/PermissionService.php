<?php

namespace App\Services\Admin;

use App\Repositories\PermissionRepository;
use Illuminate\Database\Eloquent\Collection;

class PermissionService
{
    public function __construct(private PermissionRepository $permissionRepository) {}

    public function groupedPermissions(): array
    {
        $allPermissions = $this->permissionRepository->getAll();

        return $allPermissions
            ->groupBy('group')
            ->map(function ($permissions, $group) {
                return [
                    'group' => $group,
                    'items' => $permissions->map(fn ($permission) => [
                        'id' => $permission->id,
                        'name' => $permission->name,
                    ]),
                ];
            })
            ->values()
            ->toArray();
    }

    public function groupedPermissionsWithSelected(Collection $selectedPermissions): array
    {
        $allPermissions = $this->permissionRepository->getAll();

        $selectedIds = $selectedPermissions->pluck('id')->toArray();

        return $allPermissions
            ->groupBy('group')
            ->map(function ($permissions, $group) use ($selectedIds) {
                $items = $permissions->map(function ($permission) use ($selectedIds) {
                    return [
                        'id' => $permission->id,
                        'name' => $permission->name,
                        'is_selected' => in_array($permission->id, $selectedIds),
                    ];
                });

                return [
                    'group' => $group,
                    'items' => $items,
                ];
            })
            ->values()
            ->toArray();
    }
}