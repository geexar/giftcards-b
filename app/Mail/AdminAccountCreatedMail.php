<?php

namespace App\Mail;

use App\Models\Admin;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminAccountCreatedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Admin $admin, public string $password) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Admin Account Has Been Created',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mails.admin-account-created',
            with: [
                'admin' => $this->admin,
                'password' => $this->password,
                'url' => config('app.dashboard_url'),
            ],
        );
    }
}
