<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminRequest;
use App\Http\Resources\Admin\AdminResource;
use App\Http\Resources\BaseCollection;
use App\Repositories\AdminRepository;
use App\Services\Admin\AdminService;

class AdminController extends Controller
{
    public function __construct(private AdminService $adminService, private AdminRepository $adminRepository) {}

    public function index()
    {
        $admins = $this->adminRepository->getPaginatedAdmins();

        return success(new BaseCollection($admins, AdminResource::class));
    }

    public function store(AdminRequest $request)
    {
        $this->adminService->create($request->validated());

        return success(true);
    }

    public function show(string $id)
    {
        $admin = $this->adminService->getAdmin($id);

        return success(AdminResource::make($admin));
    }

    public function update(AdminRequest $request, string $id)
    {
        $this->adminService->update($id, $request->validated());

        return success(true);
    }

    public function destroy(string $id)
    {
        $this->adminService->delete($id);

        return success(true);
    }
}
