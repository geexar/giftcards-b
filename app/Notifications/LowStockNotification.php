<?php

namespace App\Notifications;

use App\Models\Product;
use App\Notifications\Channels\FcmChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private string $titleKey = 'low_stock_title';
    private string $bodyKey  = 'low_stock_body';

    public function __construct(private Product $product) {}

    public function via(object $notifiable): array
    {
        return ['database', FcmChannel::class];
    }

    private function getParams(string $locale): array
    {
        return [
            'product' => $this->product->getTranslation('name', $locale),
            'stock'   => $this->product->total_stock,
        ];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => [
                'en' => __($this->titleKey, $this->getParams('en'), 'en'),
                'ar' => __($this->titleKey, $this->getParams('ar'), 'ar'),
            ],
            'body' => [
                'en' => __($this->bodyKey, $this->getParams('en'), 'en'),
                'ar' => __($this->bodyKey, $this->getParams('ar'), 'ar'),
            ],
            'data' => [
                'type' => 'inventory',
                'product_id' => $this->product->id,
            ],
        ];
    }

    public function toFcm(object $notifiable): array
    {
        $locale = $notifiable->app_locale ?? 'en';

        return [
            'title' => __($this->titleKey, $this->getParams($locale), $locale),
            'body'  => __($this->bodyKey, $this->getParams($locale), $locale),
            'data' => [
                'notification_id' => $this->id,
                'type' => 'inventory',
                'product_id' => $this->product->id,
            ],
        ];
    }
}
