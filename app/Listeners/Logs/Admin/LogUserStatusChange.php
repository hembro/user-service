<?php

declare(strict_types=1);

namespace App\Listeners\Logs\Admin;

use App\Events\Admin\UserStatusUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

final class LogUserStatusChange implements ShouldQueue
{
    public string $queue = 'low';

    public int $tries = 3;

    public function handle(UserStatusUpdated $event): void
    {
        Log::channel('audit')->info(
            message: 'admin updated user status',
            context: [
                'user_id' => $event->user->id,
                'admin_id' => $event->admin->id,
                'old_status' => $event->oldStatus->value,
                'new_status' => $event->newStatus->value,
            ]
        );
    }
}
