<?php

declare(strict_types=1);

namespace App\Listeners\Logs\Users;

use App\Events\Users\UserEmailChangeRequested;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

final class LogUserEmailChangeRequested implements ShouldQueue
{
    public function handle(UserEmailChangeRequested $event): void
    {
        Log::channel('audit')->info(
            message: 'user requested email change',
            context: [
                'user_id' => $event->user->id,
                'new_email' => $event->newEmail,
            ]
        );
    }
}
