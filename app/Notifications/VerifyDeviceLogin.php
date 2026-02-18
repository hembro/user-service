<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class VerifyDeviceLogin extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [10, 60];

    public function __construct(
        private readonly string $otpCode,
        private readonly string $userAgent
    ) {
        $this->queue = 'high';
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Device Verification Code')
            ->greeting('Hello!')
            ->line('We detected a login attempt from a new device: ' . $this->userAgent)
            ->line('Please use the following code to verify your identity:')
            ->line($this->otpCode)
            ->line('This code will expire in 5 minutes.')
            ->line('If you did not attempt this login, please change your password immediately.');
    }
}
