<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\UserCreated;
use App\Jobs\Tasks\ProcessWelcomeEmail;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

final class SendWelcomeNotification implements ShouldHandleEventsAfterCommit
{
    public function __construct() {}

    public function handle(UserCreated $event): void
    {
        ProcessWelcomeEmail::dispatch($event->user);
    }
}
