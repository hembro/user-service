<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\UserCreated;
use App\Jobs\Tasks\ProcessWelcomeEmail;

final class SendWelcomeNotification
{
    public function __construct() {}

    public function handle(UserCreated $event): void
    {
        ProcessWelcomeEmail::dispatch($event->user)->afterResponse();
    }
}
