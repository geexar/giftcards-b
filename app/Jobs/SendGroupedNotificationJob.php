<?php

namespace App\Jobs;

use App\Models\GroupedNotification as GroupedNotificationModel;
use App\Notifications\GroupedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Notification;
use App\Models\User;

class SendGroupedNotificationJob implements ShouldQueue
{
    use Queueable;

    public function __construct(private GroupedNotificationModel $groupedNotification) {}

    public function handle(): void
    {
        $notification = new GroupedNotification($this->groupedNotification);

        if ($this->groupedNotification->sent_to_all) {
            // Send to all active users in chunks
            User::where('is_active', 1)
                ->chunk(500, function ($users) use ($notification) {
                    Notification::send($users, $notification);
                });

            // Send to selected users
        } else {
            $users = $this->groupedNotification->users;
            Notification::send($users, $notification);
        }
    }
}
