<?php

declare(strict_types=1);

namespace App\Listeners\Notifications;

use App\Events\UserRegistered;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Contracts\Queue\ShouldQueue;

final class SendVerificationNotification implements ShouldHandleEventsAfterCommit, ShouldQueue
{
    public string $queue = 'high';

    public int $tries = 3;

    public function handle(UserRegistered $event): void
    {
        $event->user->sendEmailVerificationNotification();
    }
}
