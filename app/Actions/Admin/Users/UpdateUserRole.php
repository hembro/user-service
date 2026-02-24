<?php

declare(strict_types=1);

namespace App\Actions\Admin\Users;

use App\Commands\Admin\Users\UpdateRoleCommand;
use App\Enums\Roles;
use App\Events\Admin\UserRoleUpdated;
use App\Services\Auth\SystemTokenRevoker;
use App\Services\Users\SyncSystemRoles;
use Illuminate\Database\DatabaseManager;

final readonly class UpdateUserRole
{
    public function __construct(
        private DatabaseManager $db,
        private SyncSystemRoles $syncSystemRoles,
        private SystemTokenRevoker $tokenRevoker
    ) {}

    public function handle(UpdateRoleCommand $command): void
    {
        Roles::ensureBelongsToSystem($command->roles, $command->system);

        $this->db->transaction(
            callback: function () use ($command): void {

                $changes = $this->syncSystemRoles->handle(
                    user: $command->targetUser,
                    system: $command->system,
                    newRoles: $command->roles
                );

                if ($changes === null) {
                    return;
                }

                $this->tokenRevoker->revoke($command->targetUser, $command->system);

                $command->targetUser->touch();

                UserRoleUpdated::dispatch($command->targetUser, $command->actor, $changes['old'], $changes['new'], $command->system);
            }
        );
    }
}
