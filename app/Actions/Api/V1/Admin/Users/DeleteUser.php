<?php

declare(strict_types=1);

namespace App\Actions\Api\V1\Admin\Users;

use App\Events\Admin\UserDeleted;
use App\Models\User;
use Illuminate\Database\DatabaseManager;

final readonly class DeleteUser
{
    public function __construct(
        private DatabaseManager $db
    ) {}

    public function handle(User $user, User $admin): void
    {
        $this->db->transaction(
            callback: function () use ($user, $admin): void {
                $user->tokens()->delete();

                $user->delete();

                UserDeleted::dispatch($user->email, $admin);
            }
        );
    }
}
