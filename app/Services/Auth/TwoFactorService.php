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

    public function verify(User $user, string $code): bool
    {
        if (! $user->two_factor_secret) {
            return false;
        }

        return $this->engine->verifyKey($user->two_factor_secret, $code);
    }

    public function generateRecoveryCodes(): Collection
    {
        return Collection::times(8, fn () => Str::random(10) . '-' . Str::random(10));
    }
}
