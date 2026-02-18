<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

final readonly class TwoFactorService
{
    public function __construct(
        private Google2FA $engine
    ) {}

    public function generateSecretKey(): string
    {
        return $this->engine->generateSecretKey();
    }

    public function generateQrCodeUrl(User $user, string $secret): string
    {
        return $this->engine->getQRCodeUrl(
            company: config('app.name'),
            holder: $user->email,
            secret: $secret
        );
    }

    public function generateRecoveryCodes(): Collection
    {
        return Collection::times(8, fn () => Str::random(10) . '-' . Str::random(10));
    }

    public function valid(User $user, string $code): bool
    {
        return match (mb_strlen($code)) {
            6 => $this->validTotp($user, $code),
            21 => $this->validRecoveryCode($user, $code),
            default => false,
        };
    }

    public function validTotp(User $user, string $code): bool
    {
        if (! $user->two_factor_secret) {
            return false;
        }

        return $this->engine->verifyKey($user->two_factor_secret, $code, window: 1);
    }

    public function disable(User $user): void
    {
        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();
    }

    private function validRecoveryCode(User $user, string $recoveryCode): bool
    {
        $recoveryCodes = $user->two_factor_recovery_codes;

        if (! $recoveryCodes || $recoveryCodes->isEmpty()) {
            return false;
        }

        $key = $recoveryCodes->search($recoveryCode, strict: true);

        if ($key === false) {
            return false;
        }

        $recoveryCodes->forget($key);

        $user->forceFill(['two_factor_recovery_codes' => $recoveryCodes->values()])->save();

        return true;
    }
}
