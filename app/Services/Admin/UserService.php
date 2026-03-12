<?php

namespace App\Services\Admin;

use App\Repositories\UserRepository;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserService
{
    public function __construct(private UserRepository $userRepository) {}

    public function getUser(string $id)
    {
        $user = $this->userRepository->getById($id);

        if (!$user) {
            throw new NotFoundHttpException('user not found');
        }

        return $user;
    }

    public function create(array $data)
    {
        if (isset($data['phone'])) {
            $data['phone'] = normalizePhoneNumber($data['phone']);
        }

        DB::transaction(function () use ($data) {
            $user = $this->userRepository->create($data);

            if (isset($data['image'])) {
                $user->addMedia($data['image'])->toMediaCollection();
            }
        });
    }

    public function update(string $id, array $data)
    {
        $user = $this->getUser($id);

        if (isset($data['phone'])) {
            $data['phone'] = normalizePhoneNumber($data['phone']);
        }

        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        DB::transaction(function () use ($user, $data) {
            $this->userRepository->update($user, $data);

            if (isset($data['image'])) {
                $user->addMedia($data['image'])->toMediaCollection();
            }

            if (!$data['is_active']) {
                $user->tokens()->delete();
            }
        });
    }

    public function delete(string $id)
    {
        $user = $this->getUser($id);

        DB::transaction(function () use ($user) {
            $user->delete();
            $this->userRepository->invalidateUserData($user);
        });
    }
}
