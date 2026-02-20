<?php

declare(strict_types=1);

namespace App\Actions\Api\V1\Admin\Users;

use App\DTOs\Api\V1\Admin\Users\ResetPasswordData;
use App\Events\Admin\UserPasswordReset;
use Illuminate\Database\DatabaseManager;

final readonly class ResetPassword
{
    public function __construct(
        private DatabaseManager $db,
    ) {}

    public function handle(ResetPasswordData $dto): void
    {
        $this->db->transaction(
            callback: function () use ($dto): void {

                $dto->targetUser->update(['password' => $dto->password]);

                $dto->targetUser->tokens()->delete();

                UserPasswordReset::dispatch($dto->targetUser, $dto->actor, $dto->system);
            }
        );
    }
}
