<?php

declare(strict_types=1);

namespace App\Actions\Api\V1\Users;

use App\DTOs\Api\V1\Users\UpdatePasswordDTO;
use App\Events\Users\UserPasswordUpdated;
use App\Models\User;
use Illuminate\Database\DatabaseManager;

final readonly class UpdatePassword
{
    public function __construct(
        private DatabaseManager $db
    ) {}

    public function handle(UpdatePasswordDTO $dto, User $user): void
    {
        $this->db->transaction(
            callback: function () use ($dto, $user): void {

                $user->update([
                    'password' => $dto->newPassword,
                ]);

                $currentAccessToken = $user->currentAccessToken();

                if ($currentAccessToken !== null) {
                    $user->tokens()->where('id', '!=', $currentAccessToken->id)->delete();
                }

                $this->db->afterCommit(
                    fn () => UserPasswordUpdated::dispatch($user)
                );
            }
        );
    }
}
