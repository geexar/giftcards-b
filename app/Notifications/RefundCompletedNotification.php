<?php

namespace App\Notifications;

use App\Models\Order;
use App\Notifications\Channels\FcmChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class RefundCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private string $titleKey = 'refund_completed_title';
    private string $bodyKey = 'refund_completed_body';

    public function __construct(private Order $order) {}

    public function via(object $notifiable): array
    {
        return ['database', FcmChannel::class];
    }

    private function getParams(): array
    {
        return [
            'order_no' => $this->order->order_no,
            'amount'   => formatMoney($this->order->refund->amount),
        ];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => [
                'en' => __($this->titleKey, $this->getParams(), 'en'),
                'ar' => __($this->titleKey, $this->getParams(), 'ar'),
            ],
            'body' => [
                'en' => __($this->bodyKey, $this->getParams(), 'en'),
                'ar' => __($this->bodyKey, $this->getParams(), 'ar'),
            ],
            'data' => [
                'type'     => 'order',
                'order_no' => $this->order->order_no,
            ],
        ];
    }

    public function toFcm(object $notifiable): array
    {
        $locale = $notifiable->app_locale ?? 'en';

        return [
            'title' => __($this->titleKey, $this->getParams(), $locale),
            'body'  => __($this->bodyKey, $this->getParams(), $locale),
            'data' => [
                'notification_id' => $this->id,
                'type'            => 'order',
                'order_no'        => $this->order->order_no,
            ],
        ];
    }
}
