<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\UserCreated;

final class PublishUserCreationToBroker
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(UserCreated $event): void
    {
        //
    }
}
