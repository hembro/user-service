<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class PasswordResetByAdmin extends Notification implements ShouldQueue
{
    use Queueable;

    public string $queue = 'high';

    public int $tries = 3;

    public array $backoff = [10, 60];

    public function __construct(
        public string $adminName
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /** @param  \App\Models\User $notifiable */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Security Alert: Your password has been reset')
            ->greeting('Hello ' . $notifiable->profile?->first_name . ',')
            ->line("This is a security notification to inform you that your account password was manually reset by an administrator ({$this->adminName}).")
            ->line('All your existing active sessions have been terminated for security purposes.')
            ->line('If you requested this change, you can ignore this email.')
            ->line('If you did NOT request this, please contact the us immediately.')
            ->salutation('Regards, System Security');
    }
}
