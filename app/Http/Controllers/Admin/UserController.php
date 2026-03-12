<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRequest;
use App\Http\Resources\Admin\UserResource;
use App\Http\Resources\BaseCollection;
use App\Repositories\UserRepository;
use App\Services\Admin\UserService;

class UserController extends Controller
{
    public function __construct(private UserService $userService, private UserRepository $userRepository) {}

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
}
