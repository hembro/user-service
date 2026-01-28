<?php

declare(strict_types=1);

namespace App\Jobs\Tasks;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

final class ProcessUserLoggedIn implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public User $user,
        public string $ip,
        public string $userAgent,
        public string $timestamp
    ) {}

    public function handle(): void
    {
        $this->user->updateQuietly(['last_login_at' => now()]);

        Log::info('User Loggedin', [
            'user_id' => $this->user->id,
            'ip' => $this->ip,
            'user_agent' => $this->userAgent,
            'timestamp' => $this->timestamp,
        ]);
    }
}
