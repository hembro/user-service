<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Contracts\Auth\DeviceTrustVerifier;
use App\DTOs\Api\V1\Auth\AuthChallengeData;
use App\DTOs\Api\V1\Auth\AuthenticationOutcomeDTO;
use App\DTOs\Api\V1\Auth\LoginData;
use App\DTOs\Api\V1\Shared\RequestMetadata;
use App\Enums\Auth\ChallengeType;
use App\Enums\Systems;
use App\Enums\UserStatus;
use App\Events\Auth\DeviceVerificationRequested;
use App\Events\Auth\UserLoggedIn;
use App\Exceptions\InvalidCredentialsException;
use App\Models\User;
use App\Services\Auth\ChallengeService;
use App\Services\Auth\TokenIssuer;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final readonly class AttemptLogin
{
    public function __construct(
        private DeviceTrustVerifier $deviceService,
        private ChallengeService $challengeService,
        private TokenIssuer $tokenIssuer,
        private DatabaseManager $db
    ) {}

    public function handle(LoginData $dto): AuthenticationOutcomeDTO
    {
        $user = User::query()
            ->with('roles')
            ->where('email', $dto->email)
            ->first();

        if (! $user || $user->status !== UserStatus::ACTIVE || ! Hash::check($dto->password, $user->password)) {
            throw new InvalidCredentialsException('Invalid credentials.');
        }

        return $this->db->transaction(
            callback: function () use ($user, $dto): AuthenticationOutcomeDTO {

                if ($this->deviceService->isTrusted($user, $dto->deviceId, $dto->metadata)) {

                    $user->touchLastLoginAt();

                    UserLoggedIn::dispatch($user, $dto->deviceId, $dto->metadata);

                    return AuthenticationOutcomeDTO::authenticated(
                        token: $this->tokenIssuer->issueFullToken($user, $dto->system),
                        deviceId: $dto->deviceId
                    );
                }

                // Initiate challenge if user device is not trusted.
                $challengeType = $user->hasEnabledTwoFactor()
                    ? ChallengeType::TWO_FACTOR
                    : ChallengeType::DEVICE_VERIFICATION;

                return $this->initiateChallenge($user, $challengeType, $dto->deviceId, $dto->system, $dto->metadata);
            }
        );
    }

    private function initiateChallenge(User $user, ChallengeType $type, string $deviceId, Systems $system, RequestMetadata $metadata): AuthenticationOutcomeDTO
    {
        $challengeId = Str::uuid()->toString();

        $otpCode = match ($type) {
            ChallengeType::TWO_FACTOR => null,
            ChallengeType::DEVICE_VERIFICATION => (string) random_int(100000, 999999)
        };

        $challengeDto = new AuthChallengeData(
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
            DeviceVerificationRequested::dispatch($user, $otpCode, $system, $metadata);
        }

        return AuthenticationOutcomeDTO::challenge(
            challengeId: $challengeId,
            challengeType: $type,
            deviceId: $deviceId
        );
    }
}
