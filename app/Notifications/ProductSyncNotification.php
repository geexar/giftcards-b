<?php

namespace App\Notifications;

use App\Enums\ProductSyncStatus;
use App\Models\ProductSyncLog;
use App\Notifications\Channels\FcmChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ProductSyncNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private string $titleKey;
    private string $bodyKey;

    public function __construct(private ProductSyncLog $productSyncLog)
    {
        // Set keys based on sync status
        $this->titleKey = $productSyncLog->status === ProductSyncStatus::SUCCESS
            ? 'product_sync_success_title'
            : 'product_sync_failed_title';

        $this->bodyKey = $productSyncLog->status === ProductSyncStatus::SUCCESS
            ? 'product_sync_success_body'
            : 'product_sync_failed_body';
    }

    public function via(object $notifiable): array
    {
        return ['database', FcmChannel::class];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'status' => $this->productSyncLog->status->value,
            'title' => [
                'en' => __($this->titleKey, [], 'en'),
                'ar' => __($this->titleKey, [], 'ar'),
            ],
            'body' => [
                'en' => __($this->bodyKey, [], 'en'),
                'ar' => __($this->bodyKey, [], 'ar'),
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
            'title' => __($this->titleKey, [], $locale),
            'body'  => __($this->bodyKey, [], $locale),
            'data' => [
                'notification_id' => $this->id,
                'type'            => 'alert',
            ],
        ];
    }
}
