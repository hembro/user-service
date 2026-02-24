<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Commands\Auth\VerifyChallengeCommand;
use App\DTOs\Auth\AuthenticationOutcome;
use App\DTOs\Auth\CachedAuthChallenge;
use App\Enums\Auth\ChallengeType;
use App\Events\Auth\UserLoggedIn;
use App\Exceptions\Auth\InvalidChallengeException;
use App\Models\User;
use App\Services\Auth\ChallengeService;
use App\Services\Auth\DeviceTrustService;
use App\Services\Auth\TokenIssuer;
use App\Services\Auth\TwoFactorService;
use Illuminate\Database\DatabaseManager;
use Psr\Log\LoggerInterface;

final readonly class VerifyAuthenticationChallenge
{
    public function __construct(
        private DatabaseManager $db,
        private TokenIssuer $tokenIssuer,
        private DeviceTrustService $deviceService,
        private TwoFactorService $twoFactorService,
        private ChallengeService $challengeService,
        private LoggerInterface $logger
    ) {}

    public function handle(VerifyChallengeCommand $command): AuthenticationOutcome
    {
        $cachedData = $this->challengeService->retrieve($command->challengeId);

        if ($cachedData === null) {
            throw new InvalidChallengeException('Challenge expired or invalid.');
        }

        $challenge = CachedAuthChallenge::fromCache($cachedData);

        // Anti-Hijacking: Ensure the fingerprint hasn't changed.
        if (! $this->challengeService->validFingerprint($challenge->fingerprint, $command->metadata)) {

            $this->logger->alert(
                'Session hijacking attempt detected during authentication challenge.',
                [
                    'challenge_id' => $command->challengeId,
                    'expected_fingerprint' => $challenge->fingerprint,
                    'actual_ip' => $command->metadata->ip,
                    'actual_user_agent' => $command->metadata->userAgent,
                ]
            );

            throw new InvalidChallengeException('Security mismatch. Please login again.');
        }

        $user = User::query()->with('roles')->findOrFail($challenge->userId);

        $isValid = match ($challenge->type) {
            ChallengeType::TWO_FACTOR => $this->twoFactorService->valid($user, $command->code),
            ChallengeType::DEVICE_VERIFICATION => $this->challengeService->validOtp($challenge->otpHash, $command->code),
            default => throw new InvalidChallengeException('Invalid challenge type.')
        };

        if (! $isValid) {

            $strikes = $this->challengeService->incrementStrike($command->challengeId);

            if ($strikes >= 3) {

                $this->challengeService->forget($command->challengeId);

                $this->logger->warning('Auth challenge destroyed due to multiple attempt.', ['user_id' => $user->id]);

                throw new InvalidChallengeException('Too many failed attempts. Please login again.');
            }

            throw new InvalidChallengeException('Invalid code. You have ' . (3 - $strikes) . ' attempts remaining.');
        }

        $outcome = $this->db->transaction(
            callback: function () use ($user, $challenge, $command): AuthenticationOutcome {

                $this->deviceService->trustDevice($user, $challenge->deviceId, $command->metadata);

                UserLoggedIn::dispatch($user, $challenge->deviceId, $command->system, $command->metadata);

                return AuthenticationOutcome::authenticated(
                    token: $this->tokenIssuer->issueFullToken($user, $challenge->system),
                    deviceId: $challenge->deviceId,
                );
            }
        );

        $this->challengeService->forget($command->challengeId);

        return $outcome;
    }
}
