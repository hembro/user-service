<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\UserCreated;
use App\Jobs\Publishers\PublishUserCreated;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

final class PublishUserCreationToBroker implements ShouldHandleEventsAfterCommit
{
    public function __construct() {}

    public function handle(UserCreated $event): void
    {
        PublishUserCreated::dispatch($event->user);
    }
}
