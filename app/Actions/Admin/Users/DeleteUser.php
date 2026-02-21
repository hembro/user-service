<?php

declare(strict_types=1);

namespace App\Actions\Admin\Users;

use App\Commands\Admin\Users\DeleteUserCommand;
use App\Events\Admin\UserDeleted;
use Illuminate\Database\DatabaseManager;

final readonly class DeleteUser
{
    public function __construct(
        private DatabaseManager $db,
    ) {}

    public function handle(DeleteUserCommand $command): void
    {
        $this->db->transaction(
            callback: function () use ($command): void {

                $command->targetUser->tokens()->delete();

                $command->targetUser->delete();

                UserDeleted::dispatch($command->targetUser->id, $command->actor, $command->system);
            }
        );
    }
}
