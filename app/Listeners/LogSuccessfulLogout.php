<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\UserLoggedOut;
use App\Jobs\Publishers\PublishUserLoggedOut;

final class LogSuccessfulLogout
{
    public function __construct() {}

    public function handle(UserLoggedOut $event): void
    {
        PublishUserLoggedOut::dispatch($event->user);
    }
}
