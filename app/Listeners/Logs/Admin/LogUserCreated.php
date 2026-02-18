<?php

declare(strict_types=1);

namespace App\Listeners\Logs\Admin;

use App\Events\Admin\UserInvited;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

final class LogUserCreated implements ShouldQueue
{
    public string $queue = 'low';

    public int $tries = 3;

    public function handle(UserInvited $event): void
    {
        Log::channel('audit')->info(
            message: 'admin created user',
            context: [
                'target_user_id' => $event->user->id,
                'admin_id' => $event->admin->id,
            ]
        );
    }
}
