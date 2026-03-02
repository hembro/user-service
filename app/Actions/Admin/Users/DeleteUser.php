<?php

declare(strict_types=1);

namespace App\Actions\Admin\Users;

use App\Commands\Admin\Users\DeleteUserCommand;
use App\Events\Admin\UserDeleted;
use Illuminate\Database\DatabaseManager;
use jeremyaliparo\IntegrationSchemas\Enums\Users\UserStatus;

final readonly class DeleteUser
{
    public function __construct(
        private DatabaseManager $db,
    ) {}

    public function handle(DeleteUserCommand $command): void
    {
        if ($command->targetUser->status === UserStatus::DELETED) {
            return;
        }

        $this->db->transaction(
            callback: function () use ($command): void {

                $command->targetUser->tokens()->delete();

                $originalEmail = $command->targetUser->email;

                $command->targetUser->updateQuietly([
                    'email' => sprintf('%s::deleted_%s', $originalEmail, now()->timestamp),
                    'status' => UserStatus::DELETED,
                ]);

                $command->targetUser->profile?->updateQuietly([
                    'first_name' => 'Deleted',
                    'last_name' => 'User',
                    'mobile_number' => null,
                    'avatar_path' => null,
                ]);

                UserDeleted::dispatch(
                    $command->targetUser->id,
                    $command->actor,
                    $command->system,
                );
            }
        );
    }
}
