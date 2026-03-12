<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderItemCanceledMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $productName;

    public function __construct(public Order $order, public OrderItem $orderItem)
    {
        $this->productName = $this->orderItem->product->name;

        if ($this->orderItem->variantValue) {
            $this->productName .= " - " . $this->orderItem->variantValue->value;
        }
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Item Canceled: {$this->productName} (Order #{$this->order->order_no})",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mails.order-item-canceled',
            with: [
                'order' => $this->order,
                'orderItem' => $this->orderItem,
                'productName' => $this->productName,
            ],
        );
    }
}
