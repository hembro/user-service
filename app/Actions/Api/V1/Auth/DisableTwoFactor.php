<?php

declare(strict_types=1);

namespace App\Actions\Api\V1\Auth;

use App\Contracts\Auth\DeviceTrustVerifier;
use App\DTOs\Api\V1\Auth\DisableTwoFactorDTO;
use App\DTOs\Api\V1\Shared\RequestMetadata;
use App\Events\Auth\TwoFactorDisabled as TwoFactorDisabledEvent;
use App\Exceptions\Auth\InvalidChallengeException;
use App\Models\User;
use App\Notifications\TwoFactorDisabled as TwoFactorDisabledNotification;
use App\Services\Auth\TwoFactorService;
use Illuminate\Database\DatabaseManager;

final readonly class DisableTwoFactor
{
    public function __construct(
        private DeviceTrustVerifier $deviceService,
        private TwoFactorService $twoFactorService,
        private DatabaseManager $db,
    ) {}

    public function handle(User $user, DisableTwoFactorDTO $dto, RequestMetadata $metadata): void
    {
        if (! $user->isTwoFactorEnabled()) {
            throw new InvalidChallengeException('Two-factor authentication is not enabled.');
        }

        if (! $this->deviceService->isTrusted($user, $dto->deviceId, $metadata)) {
            throw new InvalidChallengeException('Security mismatch. Please login again.');
        }

        $this->db->transaction(
            callback: function () use ($user, $dto, $metadata) {

                $this->twoFactorService->disable($user);

                $this->db->afterCommit(
                    function () use ($user, $dto, $metadata): void {

                        $user->notify(
                            instance: new TwoFactorDisabledNotification($user, $dto->system, $metadata)
                        );

                        TwoFactorDisabledEvent::dispatch($user, $metadata);
                    }
                );
            }
        );
    }
}
