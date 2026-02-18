<?php

declare(strict_types=1);

namespace App\Actions\Api\V1\Auth;

use App\Exceptions\Auth\InvalidTwoFactorRequest;
use App\Models\User;
use App\Services\Auth\TwoFactorService;
use Illuminate\Support\Collection;

final readonly class RegenerateRecoveryCodes
{
    public function __construct(
        private TwoFactorService $twoFactorService
    ) {}

    public function handle(User $user): Collection
    {
        if (! $user->hasEnabledTwoFactor()) {
            throw new InvalidTwoFactorRequest('Two factor authentication is not enabled.');
        }

        $newRecoveryCodes = $this->twoFactorService->generateRecoveryCodes();

        $user->forceFill([
            'two_factor_recovery_codes' => $newRecoveryCodes,
        ])->save();

        return $newRecoveryCodes;
    }
}
