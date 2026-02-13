<?php

declare(strict_types=1);

namespace App\Actions\Api\V1\Auth;

use App\Contracts\Auth\DeviceTrustVerifier;
use App\DTOs\Api\V1\Auth\AuthenticationOutcomeDTO;
use App\DTOs\Api\V1\Auth\LoginCredentials;
use App\DTOs\Api\V1\Shared\RequestMetadata;
use App\Enums\Auth\ChallengeType;
use App\Enums\UserStatus;
use App\Events\Auth\DeviceUsed;
use App\Events\Auth\UserLoggedIn;
use App\Exceptions\InvalidCredentialsException;
use App\Models\User;
use App\Notifications\VerifyDeviceLogin;
use App\Services\Auth\TokenIssuer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final readonly class AttemptLogin
{
    public function __construct(
        private DeviceTrustVerifier $deviceService,
        private TokenIssuer $tokenIssuer,
    ) {}

    public function handle(LoginCredentials $credentials, RequestMetadata $metadata): AuthenticationOutcomeDTO
    {
        $user = User::query()
            ->with('roles')
            ->where('email', $credentials->email)
            ->first();

        if (! $user || $user->status !== UserStatus::ACTIVE || ! Hash::check($credentials->password, $user->password)) {
            throw new InvalidCredentialsException('Invalid credentials.');
        }

        if ($user->hasEnabledTwoFactor()) {
            return $this->initiateChallenge($user, ChallengeType::TWO_FACTOR, $metadata);
        }

        if (! $this->deviceService->isTrusted($user, $metadata)) {
            return $this->initiateChallenge($user, ChallengeType::DEVICE_VERIFICATION, $metadata);
        }

        DeviceUsed::dispatch($user, $metadata);
        UserLoggedIn::dispatch($user, $metadata);

        return AuthenticationOutcomeDTO::authenticated(
            $this->tokenIssuer->issueFullToken($user, $credentials->system)
        );
    }

    private function initiateChallenge(User $user, ChallengeType $type, RequestMetadata $metadata): AuthenticationOutcomeDTO
    {
        $challengeId = Str::uuid()->toString();
        $otpCode = (string) random_int(100000, 999999);

        $this->deviceService->storeChallengeContext($user, $challengeId, $otpCode, $metadata);

        if ($type === ChallengeType::DEVICE_VERIFICATION) {
            $user->notify(new VerifyDeviceLogin($otpCode, $metadata->userAgent));
        }

        return AuthenticationOutcomeDTO::challenge(
            challengeId: $challengeId,
            challengeType: $type
        );
    }
}
