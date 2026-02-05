<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class AdminUpdatedUser
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public User $admin,
        public User $target
    ) {}
}
