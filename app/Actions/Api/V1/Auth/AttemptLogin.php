<?php

declare(strict_types=1);

namespace App\Actions\Api\V1\Auth;

use App\Contracts\Auth\DeviceTrustVerifier;
use App\DTOs\Api\V1\Auth\AuthChallengeDTO;
use App\DTOs\Api\V1\Auth\AuthenticationOutcomeDTO;
use App\DTOs\Api\V1\Auth\LoginCredentials;
use App\DTOs\Api\V1\Shared\RequestMetadata;
use App\Enums\Auth\ChallengeType;
use App\Enums\Systems;
use App\Enums\UserStatus;
use App\Events\Auth\UserLoggedIn;
use App\Exceptions\InvalidCredentialsException;
use App\Models\User;
use App\Notifications\VerifyDeviceLogin;
use App\Services\Auth\ChallengeService;
use App\Services\Auth\TokenIssuer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final readonly class AttemptLogin
{
    public function __construct(
        private DeviceTrustVerifier $deviceService,
        private ChallengeService $challengeService,
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

        if ($this->deviceService->isTrusted($user, $credentials->deviceId, $metadata)) {
            UserLoggedIn::dispatch($user, $credentials->deviceId, $metadata);

            return AuthenticationOutcomeDTO::authenticated(
                token: $this->tokenIssuer->issueFullToken($user, $credentials->system),
                deviceId: $credentials->deviceId
            );
        }

        // Inititate challenge if user device is not trusted.
        $chgallengeType = $user->hasEnabledTwoFactor()
            ? ChallengeType::TWO_FACTOR
            : ChallengeType::DEVICE_VERIFICATION;

        return $this->initiateChallenge($user, $chgallengeType, $credentials->deviceId, $credentials->system, $metadata);
    }

    private function initiateChallenge(User $user, ChallengeType $type, string $deviceId, Systems $system, RequestMetadata $metadata): AuthenticationOutcomeDTO
    {
        $challengeId = Str::uuid()->toString();

        $otpCode = match ($type) {
            ChallengeType::TWO_FACTOR => null,
            ChallengeType::DEVICE_VERIFICATION => (string) random_int(100000, 999999)
        };

        $challengeDto = new AuthChallengeDTO(
            userId: $user->id,
            challengeId: $challengeId,
            deviceId: $deviceId,
            type: $type,
            system: $system,
            metadata: $metadata,
            otpCode: $otpCode
        );

        // Make the challenge
        $this->challengeService->make($challengeDto);

        if ($type === ChallengeType::DEVICE_VERIFICATION) {
            $user->notify(
                new VerifyDeviceLogin($otpCode, $metadata->userAgent)
            );
        }

        return AuthenticationOutcomeDTO::challenge(
            challengeId: $challengeId,
            challengeType: $type,
            deviceId: $deviceId
        );
    }
}
