<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class UserInvited extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [10, 60];

    public function __construct(public string $adminName)
    {
        $this->queue = 'high';
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /** @param  \App\Models\User $notifiable */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('You have been invited')
            ->greeting('Hello ' . $notifiable->profile?->first_name . ',')
            ->line("This is a security notification to inform you that you have been invited by an administrator ({$this->adminName}).")
            ->line('If you did NOT request this, please contact the us immediately.')
            ->salutation('Regards, System Security');
    }
}
