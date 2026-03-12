<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RecipientGiftMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        $purchaser = $this->order->user->name ?? $this->order->name;

        return new Envelope(
            subject: "You’ve Received a Gift from $purchaser",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mails.recipient-gift',
            with: ['order' => $this->order],
        );
    }
}
