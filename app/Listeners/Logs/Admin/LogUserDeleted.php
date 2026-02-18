<?php

declare(strict_types=1);

namespace App\Listeners\Logs\Admin;

use App\Events\Admin\UserDeleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

final class LogUserDeleted implements ShouldQueue
{
    public string $queue = 'low';

    public int $tries = 3;

    public function handle(UserDeleted $event): void
    {
        Log::channel('audit')->info(
            message: 'admin deleted user',
            context: [
                'user_id' => $event->userId,
                'admin_id' => $event->admin->id,
            ]
        );
    }
}
