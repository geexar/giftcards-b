<?php

namespace App\Notifications;

use App\Models\Order;
use App\Notifications\Channels\FcmChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderProcessedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private string $titleKey;
    private string $bodyKey;
    private string $refundAmount;

    public function __construct(private Order $order)
    {
        // Set keys based on gifted status
        $this->titleKey = $order->is_gifted
            ? 'gifted_order_processed_title'
            : 'non_gifted_order_processed_title';

        $this->bodyKey = $order->is_gifted
            ? 'gifted_order_processed_body'
            : 'non_gifted_order_processed_body';

        $this->refundAmount = formatMoney($order->refund?->amount);
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
                'en' => __($this->bodyKey, ['refund_amount' => $this->refundAmount], 'en'),
                'ar' => __($this->bodyKey, ['refund_amount' => $this->refundAmount], 'ar'),
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
            'body'  => __($this->bodyKey, ['refund_amount' => $this->refundAmount], $locale),
            'data' => [
                'notification_id' => $this->id,
                'type'            => 'order',
                'order_no'        => $this->order->order_no,
            ],
        ];
    }
}
