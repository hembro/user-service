<?php

declare(strict_types=1);

namespace App\Actions\Api\V1\Auth;

use App\DTOs\Api\V1\Auth\DisableTwoFactorDTO;
use App\DTOs\Api\V1\Shared\RequestMetadata;
use App\Events\Auth\TwoFactorDisabled as TwoFactorDisabledEvent;
use App\Exceptions\Auth\InvalidTwoFactorRequest;
use App\Models\User;
use App\Notifications\TwoFactorDisabled as TwoFactorDisabledNotification;
use App\Services\Auth\TwoFactorService;
use Illuminate\Database\DatabaseManager;

final readonly class DisableTwoFactor
{
    public function __construct(
        private TwoFactorService $twoFactorService,
        private DatabaseManager $db,
    ) {}

    public function handle(User $user, DisableTwoFactorDTO $dto, RequestMetadata $metadata): void
    {
        if (! $user->hasEnabledTwoFactor()) {
            throw new InvalidTwoFactorRequest('Two-factor authentication is not enabled.');
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
