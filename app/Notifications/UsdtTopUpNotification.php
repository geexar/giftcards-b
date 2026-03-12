<?php

namespace App\Notifications;

use App\Models\Transaction;
use App\Mail\UsdtTopUpMail; // Import your Mailable
use App\Notifications\Channels\FcmChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class UsdtTopUpNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private string $titleKey = 'usdt_topup_title';
    private string $bodyKey = 'usdt_topup_body';

    public function __construct(private Transaction $transaction) {}

    public function via(object $notifiable): array
    {
        return ['database', FcmChannel::class, 'mail'];
    }

    private function getParams(): array
    {
        return [
            'amount'  => formatMoney($this->transaction->amount),
            'network' => $this->transaction->usdt_network
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
                'type' => 'alert',
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
                'type'            => 'alert',
            ],
        ];
    }

    public function toMail(object $notifiable): UsdtTopUpMail
    {
        return (new UsdtTopUpMail($this->transaction))
            ->to($notifiable->email);
    }
}
