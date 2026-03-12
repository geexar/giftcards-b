<?php

namespace App\Notifications;

use App\Mail\ProductRestockedMail;
use App\Models\ProductAvailabilitySubscription;
use App\Notifications\Channels\FcmChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ProductRestockedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private string $titleKey = 'product_restocked_title';
    private string $bodyKey = 'product_restocked_body';
    private string $itemName;

    public function __construct(private ProductAvailabilitySubscription $subscription)
    {
        // Calculate item name once in the constructor
        $name = $this->subscription->product->name;

        if ($this->subscription->variantValue) {
            $name .= ' ' . $this->subscription->variantValue->value;
        }

        $this->itemName = $name;
    }

    public function via(object $notifiable): array
    {
        return ['database', FcmChannel::class, 'mail'];
    }

    private function getParams(): array
    {
        return [
            'item' => $this->itemName,
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
                'type' => 'product',
                'product_id' => $this->subscription->product_id
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
                'type'            => 'product',
                'product_id'      => $this->subscription->product_id
            ],
        ];
    }

    public function toMail(object $notifiable): ProductRestockedMail
    {
        return (new ProductRestockedMail($this->subscription))
            ->to($this->subscription->email);
    }
}
