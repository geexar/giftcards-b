<?php

namespace App\Http\Resources\Admin;

use App\Services\Admin\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'country_code' => $this->country_code,
            'phone' => $this->phone,
            'is_active' => $this->is_active,
            'image' => $this->image?->getUrl(),
            'role' => !$this->role ? null : [
                'id' => $this->role?->id,
                'name' => $this->role?->name
            ],
            'permissions' => $this->formatPermissions($this->permissions),
        ];
    }

    private function formatPermissions($permissions)
    {
        return $permissions
            ->groupBy('group')
            ->map(function ($group) {
                return $group->map(function ($permission) {
                    return [
                        'id' => $permission->id,
                        'name' => $permission->name,
                    ];
                })->values();
            });
    }
}
