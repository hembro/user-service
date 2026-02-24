<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Commands\Auth\EnableTwoFactorCommand;
use App\DTOs\Auth\TwoFactorSetupDetails;
use App\Events\Auth\EnableTwoFactorRequested;
use App\Exceptions\Auth\InvalidTwoFactorRequest;
use App\Services\Auth\TwoFactorService;
use Illuminate\Database\DatabaseManager;

final readonly class EnableTwoFactor
{
    public function __construct(
        private DatabaseManager $db,
        private TwoFactorService $service
    ) {}

    public function handle(EnableTwoFactorCommand $command): TwoFactorSetupDetails
    {
        if ($command->user->hasEnabledTwoFactor()) {
            throw new InvalidTwoFactorRequest('Two-factor authentication is already enabled.');
        }

        $secret = $this->service->generateSecretKey();

        $this->db->transaction(
            callback: function () use ($command, $secret) {

                $command->user->forceFill([
                    'two_factor_secret' => $secret,
                    'two_factor_recovery_codes' => null,
                    'two_factor_confirmed_at' => null,
                ])->save();

                EnableTwoFactorRequested::dispatch($command->user, $command->system, $command->metadata);
            }
        );

        $qrUrl = $this->service->generateQrCodeUrl($command->user, $secret);

        return new TwoFactorSetupDetails($secret, $qrUrl);
    }
}
