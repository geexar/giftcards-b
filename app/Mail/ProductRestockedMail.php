<?php

namespace App\Mail;

use App\Models\ProductAvailabilitySubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProductRestockedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public ProductAvailabilitySubscription $subscription) {}

    public function envelope(): Envelope
    {
        // Extracting item name for the subject
        $itemName = $this->subscription->product->name;
        if ($this->subscription->variantValue) {
            $itemName .= ' ' . $this->subscription->variantValue->value;
        }

        return new Envelope(
            subject: "🎉 {$itemName} is Back in Stock!",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mails.product-restocked',
            with: [
                'product' => $this->subscription->product,
                'variantValue' => $this->subscription->variantValue,
                'url' => config('app.frontend_url') . "/products/{$this->subscription->product_id}",
            ],
        );
    }
}
