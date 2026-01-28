<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\UserLoggedOut;
use App\Jobs\Tasks\ProcessUserLoggedOut;

final class LogUserLogOutActivity
{
    public function __construct() {}

    public function handle(UserLoggedOut $event): void
    {
        ProcessUserLoggedOut::dispatch($event->user, $event->ip, $event->userAgent, $event->timestamp);
    }
}
