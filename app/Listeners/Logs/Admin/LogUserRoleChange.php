<?php

declare(strict_types=1);

namespace App\Listeners\Logs\Admin;

use App\Events\Admin\UserRoleUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

final class LogUserRoleChange implements ShouldQueue
{
    public string $queue = 'low';

    public int $tries = 3;

    public function handle(UserRoleUpdated $event): void
    {
        Log::channel('audit')->info(
            message: 'admin updated user role',
            context: [
                'user_id' => $event->user->id,
                'admin_id' => $event->admin->id,
                'old_roles' => $event->oldRoles,
                'new_roles' => $event->newRoles,
            ]
        );
    }
}
