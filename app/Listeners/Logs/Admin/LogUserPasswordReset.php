<?php

declare(strict_types=1);

namespace App\Listeners\Logs\Admin;

use App\Events\Admin\UserPasswordReset;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

final class LogUserPasswordReset implements ShouldQueue
{
    public string $queue = 'low';

    public int $tries = 3;

    public function handle(UserPasswordReset $event): void
    {
        Log::channel('audit')->info(
            message: 'admin reset user password',
            context: [
                'user_id' => $event->user->id,
                'admin_id' => $event->admin->id,
            ]
        );
    }
}
