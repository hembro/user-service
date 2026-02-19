<?php

declare(strict_types=1);

namespace App\Actions\Api\V1\Admin\Users;

use App\DTOs\Api\V1\Admin\Users\RestoreUserDTO;
use App\Events\Admin\UserRestored;
use App\Models\User;
use Illuminate\Database\DatabaseManager;

final readonly class RestoreUser
{
    public function __construct(
        private DatabaseManager $db
    ) {}

    public function handle(RestoreUserDTO $dto, User $user, User $admin): void
    {
        $this->db->transaction(
            callback: function () use ($dto, $user, $admin) {

                if (! $user->trashed()) {
                    return;
                }

                $user->restore();

                $this->db->afterCommit(
                    fn () => UserRestored::dispatch($user, $admin, $dto->system)
                );
            });
    }
}
