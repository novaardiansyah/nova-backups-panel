<?php

namespace App\Jobs;

use App\Mail\LoginOtpMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendLoginOtpEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [10, 30, 60];

    public function __construct(
        public readonly string $otp,
        public readonly string $name,
        public readonly string $email,
        public readonly string $expiresIn = '10 minutes',
    ) {}

    public function handle(): void
    {
        Mail::to($this->email)->send(
            new LoginOtpMail(
                $this->otp,
                $this->name,
                $this->email,
                $this->expiresIn,
            )
        );
    }
}
