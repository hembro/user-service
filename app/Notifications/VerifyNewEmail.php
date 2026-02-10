<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Enums\Systems;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Config;

final class VerifyNewEmail extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [10, 60];

    public function __construct(
        public readonly string $token,
        public readonly Systems $system
    ) {
        $this->queue = 'high';
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = Config::get('app.frontend.url') . '/auth/verify-email-change?' . http_build_query([
            'token' => $this->token,
            'email' => $notifiable->pending_email,
        ]);

        $systemName = $this->system->uppercase();

        return (new MailMessage)
            ->subject('Verify your new email address')
            ->greeting("Hello {$notifiable->profile?->first_name},")
            ->line("You requested to change your email address to **{$notifiable->pending_email}**.")
            ->line('Please click the button below to confirm this change.')
            ->action('Verify Email Change', $url)
            ->line('If you did not request this change, please ignore this email. Your current email will remain unchanged.')
            ->salutation("Best Regards, {$systemName} Team");
    }
}
