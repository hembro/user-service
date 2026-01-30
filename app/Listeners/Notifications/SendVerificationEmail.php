<?php

declare(strict_types=1);

namespace App\Listeners\Notifications;

use App\Events\UserCreated;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Contracts\Queue\ShouldQueue;

final class SendVerificationEmail implements ShouldHandleEventsAfterCommit, ShouldQueue
{
    public $queue = 'high-priority';

    public function handle(UserCreated $event): void
    {
        $event->user->sendEmailVerificationNotification();
    }
}
