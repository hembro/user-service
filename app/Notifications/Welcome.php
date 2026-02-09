<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Enums\Systems;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class Welcome extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [10, 60];

    public function __construct(public User $user, public Systems $system)
    {
        $this->queue = 'high';
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /** @param  User $notifiable */
    public function toMail(object $notifiable): MailMessage
    {
        $system = $this->system->uppercase();

        return (new MailMessage)
            ->subject("Welcome to {$system}")
            ->greeting("Hello {$notifiable->profile?->first_name},")
            ->line("Welcome to {$system}")
            ->line('We are excited to have you on board.')
            ->salutation("Best Regards, {$system} Team");
    }
}
