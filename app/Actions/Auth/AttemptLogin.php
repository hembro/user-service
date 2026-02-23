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
use App\Exceptions\Auth\InvalidCredentialsException;
use App\Models\User;
use App\Services\Auth\ChallengeService;
use App\Services\Auth\TokenIssuer;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Psr\Log\LoggerInterface;

final readonly class AttemptLogin
{
    public function __construct(
        private DeviceTrustVerifier $deviceService,
        private ChallengeService $challengeService,
        private TokenIssuer $tokenIssuer,
        private DatabaseManager $db,
        private LoggerInterface $logger
    ) {}

    public function handle(LoginCommand $command): AuthenticationOutcome
    {
        $user = User::query()
            ->with('roles')
            ->where('email', $command->email)
            ->first();

        // Prevent Timing Attacks
        $dummyHash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
        $passwordMatches = Hash::check(
            value: $command->password,
            hashedValue: $user ? $user->password : $dummyHash
        );

        if (! $user || ! $passwordMatches) {
            $this->logger->warning(
                'Failed login attempt: Invalid credentials.',
                [
                    'email' => $command->email,
                    'ip_address' => $command->metadata->ip,
                    'reason' => ! $user ? 'user_not_found' : 'invalid_password',
                ]
            );

            throw new InvalidCredentialsException('Invalid credentials.');
        }

        if ($user->status === UserStatus::PENDING) {
            $this->logger->notice('Login blocked: Account pending verification.', ['user_id' => $user->id]);
            throw new InvalidCredentialsException('Please verify your email address to activate your account.');
        }

        if ($user->status === UserStatus::BANNED) {
            $this->logger->notice('Login blocked: Account banned.', ['user_id' => $user->id]);
            throw new InvalidCredentialsException('This account has been suspended.');
        }

        if ($user->status === UserStatus::INACTIVE) {
            $this->logger->notice('Login blocked: Account inactive.', ['user_id' => $user->id]);
            throw new InvalidCredentialsException('This account is currently inactive. Please contact support.');
        }

        if (! $user->belongsToSystem($command->system)) {

            $this->logger->warning('Login blocked: Unauthorized system access.', [
                'user_id' => $user->id,
                'attempted_system' => $command->system->value,
            ]);

            throw new InvalidCredentialsException("You do not have authorization to access the {$command->system->uppercase()} system.");
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
        $challengeId = (string) Str::ulid();

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
