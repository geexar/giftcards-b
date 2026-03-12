<?php

namespace App\Http\Resources\Admin;

use App\Services\Admin\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleBasicResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'permissions' => $this->when(request('with_permissions'), $this->getPermissions())
        ];
    }

    private function getPermissions(): array
    {
        $permissionService = app(PermissionService::class);

        return $permissionService->groupedPermissionsWithSelected($this->permissions);
    }
}
