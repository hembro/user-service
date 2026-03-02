<?php

declare(strict_types=1);

namespace App\Actions\Admin\Users;

use App\Commands\Admin\Users\RestoreUserCommand;
use App\Events\Admin\UserRestored;
use App\Exceptions\Admin\UserRestoreCollisionException;
use App\Models\User;
use Illuminate\Database\DatabaseManager;
use jeremyaliparo\IntegrationSchemas\Enums\Users\UserStatus;

final readonly class RestoreUser
{
    public function __construct(
        private DatabaseManager $db
    ) {}

    public function handle(RestoreUserCommand $command): void
    {
        $this->db->transaction(
            callback: function () use ($command) {

                if ($command->targetUser->status !== UserStatus::DELETED) {
                    return;
                }

                $originalEmail = preg_replace('/::deleted_\d+$/', '', $command->targetUser->email);

                $emailAlreadyTaken = User::query()
                    ->where('email', $originalEmail)
                    ->exists();

                if ($emailAlreadyTaken) {
                    throw new UserRestoreCollisionException('Cannot restore user. The email address is currently registered to another active account.');
                }

                $command->targetUser->updateQuietly(['email' => $originalEmail]);

                $command->targetUser->restore();

                UserRestored::dispatch($command->targetUser, $command->actor, $command->system);
            }
        );
    }
}
