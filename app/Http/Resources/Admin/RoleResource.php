<?php

namespace App\Http\Resources\Admin;

use App\Services\Admin\PermissionService;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    public function toArray($request): array
    {
        $showRoute = $request->routeIs('admin.roles.show');
        
        $permissionService = app(PermissionService::class);
        $permissions = $permissionService->groupedPermissionsWithSelected($this->permissions);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'is_active' => $this->is_active,
            'permissions' => $this->when($showRoute, $permissions)
        ];
    }
}
