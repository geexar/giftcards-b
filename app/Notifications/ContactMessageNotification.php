<?php

namespace App\Notifications;

use App\Models\ContactMessage;
use App\Notifications\Channels\FcmChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ContactMessageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private array $messages;

    public function __construct(private ContactMessage $contactMessage)
    {
        // Static title and body
        $this->messages = [
            'title' => 'New Contact Message',
            'body'  => 'A user submitted a message. Please review it.'
        ];
    }

    public function via($notifiable): array
    {
        return ['database', FcmChannel::class];
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => [
                'en' => __($this->messages['title'], [], 'en'),
                'ar' => __($this->messages['title'], [], 'ar'),
            ],
            'body' => [
                'en' => __($this->messages['body'], [], 'en'),
                'ar' => __($this->messages['body'], [], 'ar'),
            ],
            'data' => [
                'type' => 'contact_message',
                'contact_message_id' => $this->contactMessage->id,
            ],
        ];
    }

    public function toFcm($notifiable): array
    {
        $locale = $notifiable->app_locale;

        return [
            'title' => __($this->messages['title'], [], $locale),
            'body'  => __($this->messages['body'], [], $locale),
            'data' => [
                'notification_id' => $this->id,
                'type' => 'contact_message',
                'contact_message_id' =>  $this->id,
            ],
        ];
    }
}
