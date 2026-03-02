<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Commands\Auth\DisableTwoFactorCommand;
use App\Events\Auth\TwoFactorDisabled;
use App\Exceptions\Auth\InvalidTwoFactorRequest;
use App\Services\Auth\TwoFactorService;
use Illuminate\Database\DatabaseManager;

final readonly class DisableTwoFactor
{
    public function __construct(
        private TwoFactorService $twoFactorService,
        private DatabaseManager $db,
    ) {}

    public function handle(DisableTwoFactorCommand $command): void
    {
        if (! $command->user->hasEnabledTwoFactor()) {
            throw new InvalidTwoFactorRequest('Two-factor authentication is not enabled.');
        }

        $this->db->transaction(
            callback: function () use ($command) {

                $this->twoFactorService->disable($command->user);

                TwoFactorDisabled::dispatch($command->user, $command->system);
            }
        );
    }
}
