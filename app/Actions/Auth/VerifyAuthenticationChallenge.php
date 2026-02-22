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
use Psr\Log\LoggerInterface;

final readonly class VerifyAuthenticationChallenge
{
    public function __construct(
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
                message: 'Session hijacking attempt detected during authentication challenge.',
                context: [
                    'challenge_id' => $command->challengeId,
                    'expected_fingerprint' => $challenge->fingerprint,
                    'actual_ip' => $command->metadata->ip,
                    'actual_user_agent' => $command->metadata->userAgent,
                ]
            );

            $this->challengeService->forget($command->challengeId);
            throw new InvalidChallengeException('Security mismatch. Please login again.');
        }

        $user = User::query()->findOrFail($challenge->userId);

        $this->verifyCode($challenge, $user, $command->code);

        $this->deviceService->trustDevice($user, $challenge->deviceId, $command->metadata);

        $this->challengeService->forget($command->challengeId);

        UserLoggedIn::dispatch($user, $challenge->deviceId, $command->system, $command->metadata);

        return AuthenticationOutcome::authenticated(
            token: $this->tokenIssuer->issueFullToken($user, $challenge->system),
            deviceId: $challenge->deviceId,
        );
    }

    private function verifyCode(CachedAuthChallenge $challenge, User $user, string $inputCode): void
    {
        $isValid = match ($challenge->type) {
            ChallengeType::TWO_FACTOR => $this->twoFactorService->valid($user, $inputCode),
            ChallengeType::DEVICE_VERIFICATION => $this->challengeService->validOtp($challenge->otpHash, $inputCode),
        };

        if (! $isValid) {
            throw new InvalidChallengeException('Invalid verification code.');
        }
    }
}
