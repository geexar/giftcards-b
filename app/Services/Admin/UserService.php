<?php

namespace App\Services\Admin;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Admin;
use App\Models\User;
use App\Repositories\TransactionRepository;
use App\Repositories\UserRepository;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserService
{
    public function __construct(
        private UserRepository $userRepository,
        private ActivityLogService $activityLogService,
        private TransactionService $transactionService,
        private TransactionRepository $transactionRepository
    ) {}

    public function getUser(string $id): User
    {
        $user = $this->userRepository->getById($id);

        if (!$user) {
            throw new NotFoundHttpException('user not found');
        }

        return $user;
    }

    public function create(array $data): void
    {
        $data['uuid'] = $this->generateUuid();

        if (isset($data['phone'])) {
            $data['phone'] = normalizePhoneNumber($data['phone']);
        }

        DB::transaction(function () use ($data): void {
            $user = $this->userRepository->create($data);

            if (isset($data['image'])) {
                $user->addMedia($data['image'])->toMediaCollection();
            }

            $this->activityLogService->store($user, 'user.created');
        });
    }

    public function generateUuid()
    {
        do {
            $uuid = Str::uuid()->toString();
            $exists = $this->userRepository->getByUuid($uuid);
        } while ($exists);

        return $uuid;
    }

    public function update(string $id, array $data): void
    {
        $user = $this->getUser($id);

        $oldEmail = $user->email;
        $oldPassword = $user->password;

        if (isset($data['phone'])) {
            $data['phone'] = normalizePhoneNumber($data['phone']);
        }

        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        DB::transaction(function () use ($user, $data, $oldEmail, $oldPassword): void {
            $this->userRepository->update($user, $data);

            if (isset($data['image'])) {
                $user->clearMediaCollection();
                $user->addMedia($data['image'])->toMediaCollection();
            }

            if (!$data['is_active']) {
                $user->tokens()->delete();
            }

            if ($oldEmail != $user->email) {
                $user->tokens()->delete();
            }

            // if user changed password log him out (hashed password)
            if (isset($data['password']) && !Hash::check($data['password'], $oldPassword)) {
                $user->tokens()->delete();
            }

            $this->activityLogService->store($user, 'user.updated');
        });
    }

    public function delete(string $id): void
    {
        $user = $this->getUser($id);

        DB::transaction(function () use ($user): void {
            $user->delete();
            $this->userRepository->invalidateUserData($user);

            $this->activityLogService->store($user, 'user.deleted');
        });
    }

    public function toggleStatus(string $id)
    {
        $user = $this->getUser($id);

        DB::transaction(function () use ($user) {
            $newStatus = !$user->is_active;

            $user->update(['is_active' => $newStatus]);

            if ($newStatus == false) {
                $user->tokens()->delete();
            }

            $this->activityLogService->store($user, 'user.updated');
        });
    }

    public function updateBalance(string $id, array $data): void
    {
        $user = $this->getUser($id);

        if ($data['type'] == 'add') {
            $this->addBalance($user, $data['amount'], $data['description']);
        }

        if ($data['type'] == 'deduct') {
            $this->deductBalance($user, $data['amount'], $data['description']);
        }

        $newBalance = $this->transactionRepository->getUserBalance($user->id);

        $user->update(['balance' => $newBalance]);
    }

    public function addBalance(User $user, float $amount, string $description)
    {
        $this->transactionRepository->create([
            'transaction_no' => $this->transactionService->generateTransactionNo(),
            'user_id' => $user->id,
            'type' => TransactionType::MANUAL_ADJUSTMENT,
            'actor_type' => Admin::class,
            'actor_id' => auth('admin')->id(),
            'amount' => $amount,
            'affects_wallet' => true,
            'status' => TransactionStatus::SUCCESS,
            'description' => $description,
        ]);
    }

    public function deductBalance(User $user, float $amount, string $description)
    {
        $this->transactionRepository->create([
            'transaction_no' => $this->transactionService->generateTransactionNo(),
            'user_id' => $user->id,
            'type' => TransactionType::MANUAL_ADJUSTMENT,
            'actor_type' => Admin::class,
            'actor_id' => auth('admin')->id(),
            'amount' => $amount * -1,
            'affects_wallet' => true,
            'status' => TransactionStatus::SUCCESS,
            'description' => $description,
        ]);
    }
}
