<?php

namespace App\Notifications;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Notifications\Channels\FcmChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class RefundActionRequiredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private string $titleKey = 'refund_action_required_title';
    private string $bodyKey;

    public function __construct(private Order $order)
    {
        // Dynamically set the body key based on the order outcome
        $this->bodyKey = $order->status == OrderStatus::FAILED ? 'refund_action_failed_body' : 'refund_action_processed_body';
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
                'en' => __($this->bodyKey, [], 'en'),
                'ar' => __($this->bodyKey, [], 'ar'),
            ],
            'data' => [
                'type'     => 'refund',
                'refund_id' => $this->order->refund->id,
                'refund_no' => $this->order->refund->refund_no
            ],
        ];
    }

    public function toFcm($notifiable): array
    {
        $locale = $notifiable->app_locale ?? 'en';

        return [
            'title' => __($this->titleKey, ['order_no' => $this->order->order_no], $locale),
            'body'  => __($this->bodyKey, [], $locale),
            'data' => [
                'notification_id' => $this->id,
                'type'     => 'refund',
                'refund_id' => $this->order->refund->id,
                'refund_no' =>  $this->order->refund->refund_no
            ],
        ];
    }
}
