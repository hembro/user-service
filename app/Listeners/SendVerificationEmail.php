<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\UserCreated;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;

final class SendVerificationEmail implements ShouldDispatchAfterCommit
{
    public function __construct() {}

    public function handle(UserCreated $event): void
    {
        $event->user->sendEmailVerificationNotification();
    }
}
