<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Commands\Auth\ConfirmTwoFactorCommand;
use App\Events\Auth\TwoFactorEnabled;
use App\Exceptions\Auth\InvalidCredentialsException;
use App\Services\Auth\TwoFactorService;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Collection;
use Psr\Log\LoggerInterface;

final readonly class ConfirmTwoFactor
{
    public function __construct(
        private TwoFactorService $service,
        private DatabaseManager $db,
        private LoggerInterface $logger
    ) {}

    public function handle(ConfirmTwoFactorCommand $command): Collection
    {
        if ($command->user->hasEnabledTwoFactor()) {
            throw new InvalidCredentialsException('Two-factor authentication is already enabled.');
        }

        if (! $this->service->validTotp($command->user, $command->code)) {

            $this->logger->warning(
                'Failed TOTP confirmation attempt.',
                [
                    'user_id' => $command->user->id,
                    'ip' => $command->metadata->ip,
                ]
            );

            throw new InvalidCredentialsException('Invalid two-factor code.');
        }

        $recoveryCodes = $this->service->generateRecoveryCodes();

        $this->db->transaction(
            callback: function () use ($command, $recoveryCodes) {

                $command->user->forceFill([
                    'two_factor_confirmed_at' => now(),
                    'two_factor_recovery_codes' => $recoveryCodes,
                ])->save();

                TwoFactorEnabled::dispatch($command->user, $command->system);
            }
        );

        return $recoveryCodes;
    }
}
