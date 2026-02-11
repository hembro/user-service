<?php

declare(strict_types=1);

namespace App\Listeners\Notifications;

use App\Events\Users\UserRegistered;
use App\Notifications\VerifyEmail;

final class SendVerificationNotification
{
    public function handle(UserRegistered $event): void
    {
        $event->user->notify(
            instance: new VerifyEmail
        );
    }
}
