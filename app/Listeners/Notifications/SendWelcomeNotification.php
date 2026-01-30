<?php

declare(strict_types=1);

namespace App\Listeners\Notifications;

use App\Jobs\Tasks\ProcessWelcomeEmail;
use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

final class SendWelcomeNotification implements ShouldHandleEventsAfterCommit
{
    public function handle(Verified $event): void
    {
        ProcessWelcomeEmail::dispatch($event->user);
    }
}
