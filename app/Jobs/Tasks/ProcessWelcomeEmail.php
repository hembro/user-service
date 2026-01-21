<?php

declare(strict_types=1);

namespace App\Jobs\Tasks;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

final class ProcessWelcomeEmail implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public User $user
    ) {}

    public function handle(): void
    {
        Log::info("Sending welcome email to {$this->user->email}");
    }
}
