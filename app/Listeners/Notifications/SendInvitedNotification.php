<?php

declare(strict_types=1);

namespace App\Listeners\Notifications;

use App\Events\Admin\UserInvited;
use App\Notifications\UserInvited as UserInvitedNotification;

final class SendInvitedNotification
{
    public function handle(UserInvited $event): void
    {
        $event->user->notify(
            instance: new UserInvitedNotification(
                adminName: $event->admin->profile?->full_name ?? 'Administrator',
                system: $event->system
            )
        );
    }
}
