<?php

namespace App\Http\Resources\Admin;

use App\Services\Admin\PermissionService;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    public function toArray($request): array
    {
        $showRoute = $request->routeIs('admin.roles.show');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'is_active' => (bool) $this->is_active,
            'permissions' => $this->when($showRoute, $this->getPermissions())
        ];
    }

    private function getPermissions(): array
    {
        $permissionService = app(PermissionService::class);

        return $permissionService->groupedPermissionsWithSelected($this->permissions);
    }
}
