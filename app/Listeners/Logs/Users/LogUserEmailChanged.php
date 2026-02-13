<?php

declare(strict_types=1);

namespace App\Listeners\Logs\Users;

use App\Events\Users\UserEmailChanged;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

final class LogUserEmailChanged implements ShouldQueue
{
    public string $queue = 'low';

    public int $tries = 3;

    public function handle(UserEmailChanged $event): void
    {
        Log::channel('audit')->warning(
            message: 'user confirmed email change',
            context: [
                'user_id' => $event->user->id,
                'old_email' => $event->oldEmail,
                'new_email' => $event->user->email,
            ]
        );
    }
}
