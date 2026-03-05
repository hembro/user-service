<?php

declare(strict_types=1);

namespace App\Actions\Admin\Users;

use App\Commands\Admin\Users\UpdateUserStatusCommand;
use App\Events\Admin\UserStatusUpdated;
use App\Services\Auth\SystemTokenRevoker;
use Illuminate\Database\DatabaseManager;
use InvalidArgumentException;
use jeremyaliparo\IntegrationSchemas\Enums\Users\UserStatus;

final readonly class UpdateUserStatus
{
    public function __construct(
        private DatabaseManager $db,
        private SystemTokenRevoker $tokenRevoker
    ) {}

    public function handle(UpdateUserStatusCommand $command): void
    {
        if ($command->targetUser->status === $command->status) {
            return;
        }

        if ($command->status === UserStatus::DELETED) {
            throw new InvalidArgumentException('Use the delete route to delete users.');
        }

        $this->db->transaction(
            callback: function () use ($command): void {

                $oldStatus = $command->targetUser->status;

                $command->targetUser->update(['status' => $command->status]);

                if ($command->status !== UserStatus::ACTIVE) {
                    $this->tokenRevoker->revoke($command->targetUser, $command->system);
                }

                UserStatusUpdated::dispatch($command->targetUser, $oldStatus, $command->actor, $command->system);
            }
        );
    }
}
