<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountPasswordUpdatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly string $password,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Account Password Updated Mail',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.user-password-updated',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
