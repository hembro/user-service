<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\UserLoggedIn;
use App\Jobs\Tasks\ProcessUserLoggedIn;

final class LogUserLoginActivity
{
    public function __construct() {}

    public function handle(UserLoggedIn $event): void
    {
        ProcessUserLoggedIn::dispatch($event->user, $event->ip, $event->userAgent)->afterResponse();
    }
}
