<?php

declare(strict_types=1);

namespace App\Listeners\Publishers;

use App\Events\UserLoggedOut;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

final class PublishUserLoggedOut implements ShouldQueue
{
    public $tries = 5;

    public $backoff = [10, 30, 60];

    public function handle(UserLoggedOut $event): void
    {
        Log::info('broker: UserLoggedOut event', [
            'user_id' => $event->user->id,
        ]);
    }
}
