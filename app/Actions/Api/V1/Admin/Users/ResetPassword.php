<?php

declare(strict_types=1);

namespace App\Actions\Api\V1\Admin\Users;

use App\DTOs\Api\V1\Admin\Users\ResetPasswordDTO;
use App\Events\Admin\UserPasswordReset;
use App\Models\User;
use Illuminate\Database\DatabaseManager;

final readonly class ResetPassword
{
    public function __construct(
        private DatabaseManager $db,
    ) {}

    public function handle(ResetPasswordDTO $dto, User $user, User $admin): void
    {
        $this->db->transaction(
            callback: function () use ($dto, $user, $admin): void {

                $user->update(['password' => $dto->password]);

                $user->tokens()->delete();

                $this->db->afterCommit(
                    fn () => UserPasswordReset::dispatch($user, $admin, $dto->system)
                );
            }
        );
    }
}
