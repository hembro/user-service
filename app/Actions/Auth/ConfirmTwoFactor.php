<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Exceptions\InvalidCredentialsException;
use App\Models\User;
use App\Services\Auth\TwoFactorService;
use Illuminate\Support\Collection;

final readonly class ConfirmTwoFactor
{
    public function __construct(
        private TwoFactorService $service
    ) {}

    public function handle(User $user, string $code): Collection
    {
        if ($user->hasEnabledTwoFactor()) {
            throw new InvalidCredentialsException('Two-factor authentication is already enabled.');
        }

        if (! $this->service->validTotp($user, $code)) {
            throw new InvalidCredentialsException('Invalid two-factor code.');
        }

        $recoveryCodes = $this->service->generateRecoveryCodes();

        // Activate the 2FA
        $user->forceFill([
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => $recoveryCodes,
        ])->save();

        return $recoveryCodes;
    }
}
