<?php

declare(strict_types=1);

namespace App\Actions\Api\V1\Admin\Users;

use App\Actions\Api\V1\Auth\RevokeSystemTokens;
use App\DTOs\Api\V1\Admin\Users\UpdateUserStatusDTO;
use App\Enums\UserStatus;
use App\Events\Admin\UserStatusUpdated;
use App\Models\User;
use Illuminate\Database\DatabaseManager;

final readonly class UpdateUserStatus
{
    public function __construct(
        private DatabaseManager $db,
        private RevokeSystemTokens $revokeSystemTokens
    ) {}

    public function handle(UpdateUserStatusDTO $dto, User $user, User $admin): void
    {
        if ($user->status === $dto->status) {
            return;
        }

        $this->db->transaction(
            callback: function () use ($dto, $user, $admin): void {

                $oldStatus = $user->status;

                $user->update(['status' => $dto->status]);

                if ($dto->status !== UserStatus::ACTIVE) {
                    $this->revokeSystemTokens->handle($user, $dto->system);
                }

                $this->db->afterCommit(
                    fn () => UserStatusUpdated::dispatch($user, $admin, $oldStatus, $dto->status)
                );
            }
        );
    }
}
