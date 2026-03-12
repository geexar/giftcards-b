<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RoleRequest;
use App\Http\Resources\Admin\RoleResource;
use App\Http\Resources\BaseCollection;
use App\Repositories\RoleRepository;
use App\Services\Admin\RoleService;

class RoleController extends Controller
{
    public function __construct(private RoleService $roleService, private RoleRepository $roleRepository) {}

    public function index()
    {
        $roles = $this->roleRepository->getPaginatedRoles();

        return success(new BaseCollection($roles, RoleResource::class));
    }

    public function store(RoleRequest $request)
    {
        $this->roleService->create($request->validated());

        return success(true);
    }

    public function show(string $id)
    {
        $role = $this->roleService->getRole($id);

        return success(RoleResource::make($role));
    }

    public function update(RoleRequest $request, string $id)
    {
        $this->roleService->update($id, $request->validated());

        return success(true);
    }

    public function destroy(string $id)
    {
        $this->roleService->delete($id);

        return success(true);
    }
}
