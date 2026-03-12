<?php

namespace App\Services\Admin;

use App\Jobs\SendGroupedNotificationJob;
use App\Repositories\GroupedNotificationRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\DB;

class GroupedNotificationService
{
    public function __construct(
        private GroupedNotificationRepository $groupedNotificationRepository,
        private UserRepository $userRepository
    ) {}

    public function create(array $data): void
    {
        $data['sent_count'] = $data['sent_to_all'] == 1
            ? $this->userRepository->getActiveUsersCount()
            : count($data['selected_users']);

        $groupNotification = DB::transaction(function () use ($data) {

            $groupNotification = $this->groupedNotificationRepository->create($data);

            if (!$data['sent_to_all']) {
                $users = $this->userRepository->getActiveUsers($data['selected_users']);
                $groupNotification->users()->sync($users);
            }

            return $groupNotification;
        });

        dispatch(new SendGroupedNotificationJob($groupNotification));
    }
}
