<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\DTOs\Api\V1\Users\UpdatePasswordData;
use App\Events\Users\UserPasswordUpdated;
use Illuminate\Database\DatabaseManager;

final readonly class UpdatePassword
{
    public function __construct(
        private DatabaseManager $db
    ) {}

    public function handle(UpdatePasswordData $dto): void
    {
        $this->db->transaction(
            callback: function () use ($dto): void {

                $dto->user->update([
                    'password' => $dto->newPassword,
                ]);

                $currentAccessToken = $dto->user->currentAccessToken();

                if ($currentAccessToken !== null) {
                    $dto->user->tokens()->where('id', '!=', $currentAccessToken->id)->delete();
                }

                UserPasswordUpdated::dispatch($dto->user, $dto->system);
            }
        );
    }
}
