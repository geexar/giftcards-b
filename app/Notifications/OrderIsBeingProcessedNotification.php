<?php

namespace App\Notifications;

use App\Models\Order;
use App\Notifications\Channels\FcmChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class OrderIsBeingProcessedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private string $titleKey;
    private string $bodyKey;

    public function __construct(private Order $order)
    {
        // Set keys based on gifted status
        $this->titleKey = $order->is_gifted 
            ? 'gifted_order_is_being_processed_title' 
            : 'non_gifted_order_is_being_processed_title';

        $this->bodyKey = $order->is_gifted 
            ? 'gifted_order_is_being_processed_body' 
            : 'non_gifted_order_is_being_processed_body';
    }

    public function via($notifiable): array
    {
        return ['database', FcmChannel::class];
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => [
                'en' => __($this->titleKey, ['order_no' => $this->order->order_no], 'en'),
                'ar' => __($this->titleKey, ['order_no' => $this->order->order_no], 'ar'),
            ],
            'body' => [
                'en' => __($this->bodyKey, ['order_no' => $this->order->order_no], 'en'),
                'ar' => __($this->bodyKey, ['order_no' => $this->order->order_no], 'ar'),
            ],
            'data' => [
                'type'     => 'order',
                'order_no' => $this->order->order_no,
            ],
        ];
    }

    public function toFcm($notifiable): array
    {
        $locale = $notifiable->app_locale ?? 'en';

        return [
            'title' => __($this->titleKey, ['order_no' => $this->order->order_no], $locale),
            'body'  => __($this->bodyKey, ['order_no' => $this->order->order_no], $locale),
            'data' => [
                'notification_id' => $this->id,
                'type'            => 'order',
                'order_no'        => $this->order->order_no,
            ],
        ];
    }
}