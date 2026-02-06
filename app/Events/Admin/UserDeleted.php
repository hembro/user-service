<?php

declare(strict_types=1);

namespace App\Events\Admin;

use App\DTOs\Api\V1\Admin\Users\DeleteUserDTO;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class UserDeleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $userEmail,
        public User $admin,
        public DeleteUserDTO $dto
    ) {}
}
