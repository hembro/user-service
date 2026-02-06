<?php

declare(strict_types=1);

namespace App\Actions\Api\V1\Admin\Users;

use App\DTOs\Api\V1\Admin\Users\UpdateUserStatusDTO;
use App\Enums\UserStatus;
use App\Events\Admin\UserStatusUpdated;
use App\Models\User;
use Illuminate\Database\DatabaseManager;

final readonly class UpdateUserStatus
{
    public function __construct(
        private DatabaseManager $db
    ) {}

    public function handle(UpdateUserStatusDTO $dto, User $user, User $admin): void
    {
        $this->db->transaction(function () use ($dto, $user, $admin) {
            $oldStatus = $user->status;

            $user->update(['status' => $dto->status]);

            if ($dto->status !== UserStatus::ACTIVE) {
                $user->tokens()->delete();
            }

            UserStatusUpdated::dispatch(
                $user,
                $admin,
                $oldStatus,
                $dto->status,
            );
        });
    }
}
