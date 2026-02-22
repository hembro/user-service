<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Commands\Auth\LoginCommand;
use App\Contracts\Auth\DeviceTrustVerifier;
use App\DTOs\Auth\AuthenticationOutcome;
use App\DTOs\Auth\PendingAuthChallenge;
use App\DTOs\Shared\RequestMetadata;
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

    public function handle(LoginCommand $command): AuthenticationOutcome
    {
        $user = User::query()
            ->with('roles')
            ->where('email', $command->email)
            ->first();

        if (! $user || $user->status !== UserStatus::ACTIVE || ! Hash::check($command->password, $user->password)) {
            throw new InvalidCredentialsException('Invalid credentials.');
        }

        return $this->db->transaction(
            callback: function () use ($user, $command): AuthenticationOutcome {

                if ($this->deviceService->isTrusted($user, $command->deviceId, $command->metadata)) {

                    $user->touchLastLoginAt();

                    UserLoggedIn::dispatch($user, $command->deviceId, $command->system, $command->metadata);

                    return AuthenticationOutcome::authenticated(
                        token: $this->tokenIssuer->issueFullToken($user, $command->system),
                        deviceId: $command->deviceId
                    );
                }

                // Initiate challenge if user device is not trusted.
                $challengeType = $user->hasEnabledTwoFactor()
                    ? ChallengeType::TWO_FACTOR
                    : ChallengeType::DEVICE_VERIFICATION;

                return $this->initiateChallenge($user, $challengeType, $command->deviceId, $command->system, $command->metadata);
            }
        );
    }

    private function initiateChallenge(User $user, ChallengeType $type, string $deviceId, Systems $system, RequestMetadata $metadata): AuthenticationOutcome
    {
        $challengeId = Str::uuid()->toString();

        $otpCode = match ($type) {
            ChallengeType::TWO_FACTOR => null,
            ChallengeType::DEVICE_VERIFICATION => (string) random_int(100000, 999999)
        };

        $challenge = new PendingAuthChallenge(
            userId: $user->id,
            challengeId: $challengeId,
            deviceId: $deviceId,
            type: $type,
            system: $system,
            metadata: $metadata,
            otpCode: $otpCode
        );

        $this->challengeService->make($challenge);

        if ($type === ChallengeType::DEVICE_VERIFICATION) {
            DeviceVerificationRequested::dispatch($user, $otpCode, $system, $metadata);
        }

        return AuthenticationOutcome::challenge(
            challengeId: $challengeId,
            challengeType: $type,
            deviceId: $deviceId
        );
    }
}
