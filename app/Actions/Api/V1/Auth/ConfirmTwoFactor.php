<?php

declare(strict_types=1);

namespace App\Actions\Api\V1\Auth;

use App\Exceptions\InvalidCredentialsException;
use App\Models\User;
use App\Services\Auth\TwoFactorService;
use Illuminate\Support\Collection;

final readonly class ConfirmTwoFactor
{
    public function __construct(
        private TwoFactorService $service
    ) {}

    /**
     * @return Collection<int, string> The recovery codes
     */
    public function handle(User $user, string $code): Collection
    {
        // 1. Verify the OTP against the stored (but unconfirmed) secret
        if (! $this->service->verify($user, $code)) {
            throw new InvalidCredentialsException('Invalid two-factor code.');
        }

        // 2. Generate Recovery Codes
        $recoveryCodes = $this->service->generateRecoveryCodes();

        // 3. Activate 2FA
        $user->forceFill([
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => $recoveryCodes, // Encrypted by Cast
        ])->save();

        return $recoveryCodes;
    }
}
