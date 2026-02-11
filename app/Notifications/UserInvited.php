<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Enums\Systems;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class UserInvited extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [10, 60];

    public function __construct(
        public readonly string $adminName,
        public readonly Systems $system
    ) {
        $this->queue = 'high';
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /** @param  \App\Models\User $notifiable */
    public function toMail(object $notifiable): MailMessage
    {
        $systemName = $this->system->uppercase();

        return (new MailMessage)
            ->subject('You have been invited')
            ->greeting('Hello ' . $notifiable->profile?->first_name . ',')
            ->line("This is a security notification to inform you that you have been invited by an administrator ({$this->adminName}).")
            ->line('If you did NOT request this, please contact the us immediately.')
            ->salutation("Best Regards, {$systemName} Team");
    }
}
