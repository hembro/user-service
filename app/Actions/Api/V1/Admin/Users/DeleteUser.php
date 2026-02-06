<?php

declare(strict_types=1);

namespace App\Actions\Api\V1\Admin\Users;

use App\DTOs\Api\V1\Admin\Users\DeleteUserDTO;
use App\Events\Admin\UserDeleted;
use App\Models\User;
use Illuminate\Database\DatabaseManager;

final readonly class DeleteUser
{
    public function __construct(
        private DatabaseManager $db
    ) {}

    public function handle(DeleteUserDTO $dto, User $user, User $admin): void
    {
        $this->db->transaction(
            callback: function () use ($dto, $user, $admin): void {
                $user->tokens()->delete();

                $user->delete();

                UserDeleted::dispatch($user->email, $admin, $dto);
            }
        );
    }
}
