<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetPasswordOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $otp,
        public string $name,
        public string $email,
        public string $expiresIn = '10 minutes',
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Password Reset Code: {$this->otp} - Nova Backups",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reset-password-otp',
            with: [
                'otp' => $this->otp,
                'name' => $this->name,
                'email' => $this->email,
                'expiresIn' => $this->expiresIn,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
