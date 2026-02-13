<?php

declare(strict_types=1);

namespace App\Actions\Api\V1\Admin\Users;

use App\Actions\Api\V1\Auth\RevokeSystemTokens;
use App\DTOs\Api\V1\Admin\Users\UpdateRoleDTO;
use App\Enums\Roles;
use App\Events\Admin\UserRoleUpdated;
use App\Models\User;
use App\Services\Users\SyncSystemRoles;
use Illuminate\Database\DatabaseManager;

final readonly class UpdateUserRole
{
    public function __construct(
        private DatabaseManager $db,
        private SyncSystemRoles $syncSystemRoles,
        private RevokeSystemTokens $revokeSystemTokens
    ) {}

    public function handle(UpdateRoleDTO $dto, User $user, User $admin): void
    {
        Roles::ensureBelongsToSystem($dto->roles, $dto->system);

        $this->db->transaction(
            callback: function () use ($dto, $user, $admin): void {

                $changes = $this->syncSystemRoles->handle(
                    user: $user,
                    system: $dto->system,
                    newRoles: $dto->roles
                );

                if ($changes === null) {
                    return;
                }

                $this->revokeSystemTokens->handle($user, $dto->system);

                $this->db->afterCommit(
                    fn () => UserRoleUpdated::dispatch($user, $admin, $changes['old'], $changes['new'])
                );
            }
        );
    }
}
