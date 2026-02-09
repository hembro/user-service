<?php

declare(strict_types=1);

namespace App\Listeners\Notifications;

use App\Enums\Systems;
use App\Notifications\Welcome;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Context;

final class SendWelcomeNotification
{
    public function handle(Verified $event): void
    {
        /** @var \App\Models\User $user */
        $user = $event->user;

        $system = Systems::from(Context::get('source_system'));

        $user->notify(
            instance: new Welcome(
                user: $user,
                system: $system
            )
        );
    }
}
