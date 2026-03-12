<?php

namespace App\Notifications\Channels;

use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class FcmChannel
{
    public function send($notifiable, $notification)
    {
        $notificationData = $notification->toFcm($notifiable);

        $tokens = $notifiable->fcmTokens->pluck('token')->toArray();

        if (!count($tokens)) {
            return;
        }

        $title = $notificationData['title'] ?? '';
        $body = $notificationData['body'] ?? '';
        $data = $notificationData['data'] ?? [];

        $messaging = app('firebase.messaging');

        $message = CloudMessage::new()
            ->withNotification(Notification::create(title: $title, body: $body))
            ->withDefaultSounds()
            ->withData($data);

        $messaging->sendMulticast($message, $tokens);
    }
}
