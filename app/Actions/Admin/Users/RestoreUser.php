<?php

declare(strict_types=1);

namespace App\Actions\Admin\Users;

use App\Commands\Admin\Users\RestoreUserCommand;
use App\Events\Admin\UserRestored;
use Illuminate\Database\DatabaseManager;

final readonly class RestoreUser
{
    public function __construct(
        private DatabaseManager $db
    ) {}

    public function handle(RestoreUserCommand $command): void
    {
        $this->db->transaction(
            callback: function () use ($command) {

                if (! $command->targetUser->trashed()) {
                    return;
                }

                $command->targetUser->restore();

                UserRestored::dispatch($command->targetUser, $command->actor, $command->system);
            }
        );
    }
}
