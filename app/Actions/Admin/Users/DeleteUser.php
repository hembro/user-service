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
        if ($command->targetUser->trashed()) {
            return;
        }

        $this->db->transaction(
            callback: function () use ($command): void {

                $command->targetUser->tokens()->delete();

                $deletedEmail = sprintf('%s::deleted_%s', $command->targetUser->email, now()->timestamp);

                $command->targetUser->updateQuietly(['email' => $deletedEmail]);

                $command->targetUser->delete();

                UserDeleted::dispatch(
                    $command->targetUser->id,
                    $command->targetUser->profile?->first_name ?? $command->targetUser->email,
                    $command->targetUser->email,
                    $command->actor,
                    $command->system,
                    $command->metadata
                );
            }
        );
    }
}
