<?php

namespace App\Http\Controllers;

use App\Repositories\PermissionRepository;
use App\Services\Admin\PermissionService;

class PermissionController extends Controller
{
    public function __construct(
        private PermissionService $permissionService,
        private PermissionRepository $permissionRepository
    ) {}

    public function index()
    {
        $permissions = $this->permissionService->groupedPermissions();

        return success($permissions);
    }
}
