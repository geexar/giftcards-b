<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserBalanceRequest;
use App\Http\Requests\Admin\UserRequest;
use App\Http\Resources\Admin\UserResource;
use App\Http\Resources\BaseCollection;
use App\Repositories\UserRepository;
use App\Services\Admin\UserService;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class UserController extends Controller implements HasMiddleware
{
    public function __construct(
        private UserService $userService,
        private UserRepository $userRepository
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:show users', only: ['index']),
            new Middleware('can:view user', only: ['show']),
            new Middleware('can:create user', only: ['store']),
            new Middleware('can:update user', only: ['update', 'toggleStatus']),
            new Middleware('can:delete user', only: ['destroy']),
        ];
    }

    public function index()
    {
        $users = $this->userRepository->getPaginatedUsers();

        return success(new BaseCollection($users, UserResource::class));
    }

    public function store(UserRequest $request)
    {
        $this->userService->create($request->validated());

        return success(true);
    }

    public function show(string $id)
    {
        $user = $this->userService->getUser($id);

        return success(UserResource::make($user));
    }

    public function update(UserRequest $request, string $id)
    {
        $this->userService->update($id, $request->validated());

        return success(true);
    }

    public function destroy(string $id)
    {
        $this->userService->delete($id);

        return success(true);
    }

    public function toggleStatus(string $id)
    {
        $this->userService->toggleStatus($id);

        return success(true);
    }

    public function updateBalance(UpdateUserBalanceRequest $request, string $id)
    {
        $this->userService->updateBalance($id, $request->validated());

        return success(true);
    }
}
