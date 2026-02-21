<?php

declare(strict_types=1);

namespace App\Actions\Admin\Users;

use App\DTOs\Api\V1\Admin\Users\UpdateUserStatusData;
use App\Enums\UserStatus;
use App\Events\Admin\UserStatusUpdated;
use App\Services\Auth\SystemTokenRevoker;
use Illuminate\Database\DatabaseManager;

final readonly class UpdateUserStatus
{
    public function __construct(
        private DatabaseManager $db,
        private SystemTokenRevoker $tokenRevoker
    ) {}

    public function handle(UpdateUserStatusData $dto): void
    {
        if ($dto->targetUser->status === $dto->status) {
            return;
        }

        $this->db->transaction(
            callback: function () use ($dto): void {

                $oldStatus = $dto->targetUser->status;

                $dto->targetUser->update(['status' => $dto->status]);

                if ($dto->status !== UserStatus::ACTIVE) {
                    $this->tokenRevoker->revoke($dto->targetUser, $dto->system);
                }

                UserStatusUpdated::dispatch($dto->targetUser, $dto->actor, $oldStatus, $dto->status, $dto->system);
            }
        );
    }
}
