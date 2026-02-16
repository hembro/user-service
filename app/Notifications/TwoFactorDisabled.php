<?php

declare(strict_types=1);

namespace App\Notifications;

use App\DTOs\Api\V1\Shared\RequestMetadata;
use App\Enums\Systems;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

final class TwoFactorDisabled extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [10, 60];

    public function __construct(
        public readonly User $user,
        public readonly Systems $system,
        public readonly RequestMetadata $metadata
    ) {
        $this->queue = 'high';
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $resetPasswordUrl = config('app.frontend.url') . '/auth/reset-password';

        return (new MailMessage)
            ->subject('Security Alert: 2FA Disabled')
            ->greeting('Hello ' . ($notifiable->profile?->first_name ?? 'User') . ',')
            ->line('Two-factor authentication (2FA) has been disabled for your account.')
            ->line('Here are the details of this request:')
            ->line(new HtmlString('
                <div style="margin: 10px 0; padding: 10px; background-color: #f3f4f6; border-left: 4px solid #ef4444;">
                    <strong>IP Address:</strong> ' . $this->metadata->ip . '<br>
                    <strong>Device:</strong> ' . $this->metadata->userAgent . '<br>
                    <strong>Time:</strong> ' . now()->toCookieString() . '
                </div>
            '))
            ->line('If you did not request this change, your account may be compromised.')
            ->action('Secure My Account', $resetPasswordUrl)
            ->line('Please contact us immediately if this was not you.')
            ->salutation('Stay Safe, ' . $this->system->uppercase() . ' Team');
    }
}
