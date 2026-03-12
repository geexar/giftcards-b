<?php

namespace App\Http\Resources\Admin;

use App\Services\Admin\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $showRoute = $request->routeIs('admin.admins.show');

        $permissionService = app(PermissionService::class);
        $permissions = $permissionService->groupedPermissionsWithSelected($this->permissions);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'country_code' => $this->country_code,
            'phone' => $this->phone,
            'is_active' => $this->is_active,
            'image' => $this->image?->getUrl(),
            'permissions' => $this->when($showRoute, $permissions)
        ];
    }
}
