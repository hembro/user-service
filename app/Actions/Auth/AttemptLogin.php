<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Commands\Auth\LoginCommand;
use App\Contracts\Auth\DeviceTrustVerifier;
use App\DTOs\Auth\AuthenticationOutcome;
use App\Enums\Auth\ChallengeType;
use App\Enums\Systems;
use App\Enums\UserStatus;
use App\Events\Auth\UserLoggedIn;
use App\Exceptions\Auth\InvalidCredentialsException;
use App\Models\User;
use App\Services\Auth\ChallengeService;
use App\Services\Auth\TokenIssuer;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Hash;
use Psr\Log\LoggerInterface;

final readonly class AttemptLogin
{
    public function __construct(
        private DatabaseManager $db,
        private DeviceTrustVerifier $deviceService,
        private ChallengeService $challengeService,
        private TokenIssuer $tokenIssuer,
        private LoggerInterface $logger
    ) {}

    public function handle(LoginCommand $command): AuthenticationOutcome
    {
        $user = User::query()
            ->with(['profile', 'roles'])
            ->where('email', $command->email)
            ->first();

        $this->verifyPassword($user, $command);
        $this->ensureUserIsActive($user);
        $this->ensureUserHasAccess($user, $command->system);

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

                $challengeType = $user->hasEnabledTwoFactor()
                    ? ChallengeType::TWO_FACTOR
                    : ChallengeType::DEVICE_VERIFICATION;

                return $this->challengeService->initiateChallenge($user, $challengeType, $command->deviceId, $command->system, $command->metadata);
            }
        );
    }

    private function verifyPassword(?User $user, LoginCommand $command): void
    {
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
    }

    private function ensureUserIsActive(User $user): void
    {
        $message = match ($user->status) {
            UserStatus::PENDING => 'Please activate your account.',
            UserStatus::BANNED => 'This account has been suspended.',
            UserStatus::INACTIVE => 'This account is currently inactive. Please contact support.',
            default => null,
        };

        if ($message !== null) {
            $this->logger->notice("Login blocked: Account {$user->status->label()}.", ['user_id' => $user->id]);
            throw new InvalidCredentialsException($message);
        }
    }

    private function ensureUserHasAccess(User $user, Systems $system): void
    {
        if (! $user->belongsToSystem($system)) {

            $this->logger->warning('Login blocked: Unauthorized system access.', [
                'user_id' => $user->id,
                'attempted_system' => $system->value,
            ]);

            throw new InvalidCredentialsException("You do not have authorization to access the {$system->uppercase()} system.");
        }
    }
}
