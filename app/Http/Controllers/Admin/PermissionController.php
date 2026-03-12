<?php

namespace App\Http\Controllers\Admin;

use App\Services\Admin\PermissionService;
use App\Http\Controllers\Controller;

class PermissionController extends Controller
{
    public function __construct(private PermissionService $permissionService) {}

    public function index()
    {
        $permissions = $this->permissionService->groupedPermissions();

        return success($permissions);
    }
}
