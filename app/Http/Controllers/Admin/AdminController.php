<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminRequest;
use App\Http\Resources\Admin\AdminResource;
use App\Http\Resources\BaseCollection;
use App\Repositories\AdminRepository;
use App\Services\Admin\AdminService;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class AdminController extends Controller implements HasMiddleware
{
    public function __construct(private AdminService $adminService, private AdminRepository $adminRepository) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:show admins', only: ['index']),
            new Middleware('can:view admin', only: ['show']),
            new Middleware('can:create admin', only: ['store']),
            new Middleware('can:update admin', only: ['update', 'toggleStatus']),
            new Middleware('can:delete admin', only: ['destroy']),
        ];
    }

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

    public function toggleStatus(string $id)
    {
        $this->adminService->toggleStatus($id);

        return success(true);
    }
}
