<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\DTOs\Api\V1\Auth\TwoFactorSetupDTO;
use App\Exceptions\Auth\InvalidTwoFactorRequest;
use App\Models\User;
use App\Services\Auth\TwoFactorService;

final readonly class EnableTwoFactor
{
    public function __construct(
        private TwoFactorService $service
    ) {}

    public function handle(User $user): TwoFactorSetupDTO
    {
        if ($user->hasEnabledTwoFactor()) {
            throw new InvalidTwoFactorRequest('Two-factor authentication is already enabled.');
        }

        $secret = $this->service->generateSecretKey();

        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        $qrUrl = $this->service->generateQrCodeUrl($user, $secret);

        return new TwoFactorSetupDTO(
            secret: $secret,
            qrCodeUrl: $qrUrl
        );
    }
}
