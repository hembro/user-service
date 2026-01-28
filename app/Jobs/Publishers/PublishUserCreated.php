<?php

declare(strict_types=1);

namespace App\Jobs\Publishers;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

final class PublishUserCreated implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public User $user
    ) {}

    public function handle(): void
    {
        Log::info('BROKER: UserCreated event', [
            'user_id' => $this->user->id,
        ]);
    }
}
