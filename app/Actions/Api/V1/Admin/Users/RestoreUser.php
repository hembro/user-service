<?php

declare(strict_types=1);

namespace App\Actions\Api\V1\Admin\Users;

use App\DTOs\Api\V1\Admin\Users\RestoreUserData;
use App\Events\Admin\UserRestored;
use Illuminate\Database\DatabaseManager;

final readonly class RestoreUser
{
    public function __construct(
        private DatabaseManager $db
    ) {}

    public function handle(RestoreUserData $dto): void
    {
        $this->db->transaction(
            callback: function () use ($dto) {

                if (! $dto->targetUser->trashed()) {
                    return;
                }

                $dto->targetUser->restore();

                UserRestored::dispatch($dto->targetUser, $dto->actor, $dto->system);
            }
        );
    }
}
