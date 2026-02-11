<?php

declare(strict_types=1);

namespace App\Listeners\Notifications;

use App\Events\Admin\UserPasswordReset;
use App\Notifications\PasswordResetByAdmin;

final class SendPasswordResetNotification
{
    public function handle(UserPasswordReset $event): void
    {
        if ($event->admin->id === $event->user->id) {
            return;
        }

        $event->user->notify(
            new PasswordResetByAdmin(
                adminName: $event->admin->profile?->full_name ?? 'Administrator',
                system: $event->system
            )
        );
    }
}
