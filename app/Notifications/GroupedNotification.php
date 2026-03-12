<?php

namespace App\Notifications;

use App\Models\Admin;
use App\Models\User;
use App\Notifications\Channels\FcmChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class GroupedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private $groupNotification) {}

    public function via($notifiable)
    {
        $channels = ['database'];

        // always send to all admins and (only users with push notifications enabled)
        if (get_class($notifiable) == Admin::class || (get_class($notifiable) == User::class && $notifiable->push_notifications_enabled)) {
            $channels[] = FcmChannel::class;
        }

        return $channels;
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => $this->groupNotification->title,
            'body'  => $this->groupNotification->body,
            'data' => [
                'type'     => 'alert',
            ],
        ];
    }

    public function toFcm($notifiable): array
    {
        return [
            'title' => $this->groupNotification->title,
            'body'  => $this->groupNotification->body,
            'data' => [
                'notification_id' => $this->id,
                'type' => 'alert',
            ]
        ];
    }
}
