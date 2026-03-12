<?php

namespace App\Mail;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UsdtTopUpMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Transaction $transaction) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "USDT Deposit Confirmed — Your Wallet Has Been Credited",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mails.usdt-topup',
            with: ['transaction' => $this->transaction],
        );
    }
}
