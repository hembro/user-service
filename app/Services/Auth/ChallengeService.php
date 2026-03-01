<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\DTOs\Auth\AuthenticationOutcome;
use App\DTOs\Auth\PendingAuthChallenge;
use App\Enums\Auth\ChallengeType;
use App\Enums\Systems;
use App\Events\Auth\DeviceVerificationRequested;
use App\Exceptions\Auth\InvalidVerificationRequest;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Str;
use Psr\Log\LoggerInterface;

final readonly class ChallengeService
{
    private const CHALLENGE_TTL_SECONDS = 300;

    private const CACHE_PREFIX = 'auth:challenge:';

    private const STRIKES_PREFIX = 'strikes:';

    public function __construct(
        private OtpService $otpService,
        private LoggerInterface $logger
    ) {}

    public function retrieve(string $challengeId): ?array
    {
        $data = Cache::get(self::CACHE_PREFIX . $challengeId);

        return is_array($data) ? $data : null;
    }

    public function forget(string $challengeId): void
    {
        Cache::forget(self::CACHE_PREFIX . $challengeId);
        Cache::forget(self::CACHE_PREFIX . self::STRIKES_PREFIX . $challengeId);
    }

    public function generateFingerprint(): string
    {
        $userAgent = (string) Context::get('user_agent', 'unknown');
        $clientType = (string) Context::get('client_type', 'unknown');

        return hash_hmac(
            algo: 'sha256',
            data: $userAgent . '|' . $clientType,
            key: Config::string('app.key')
        );
    }

    public function validFingerprint(string $fingerprint): bool
    {
        return hash_equals($fingerprint, $this->generateFingerprint());
    }

    public function validOtp(?string $storedHash, string $inputCode): bool
    {
        if (! $storedHash || $storedHash === null) {
            return false;
        }

        return $this->otpService->verify($storedHash, $inputCode);
    }

    public function incrementStrike(string $challengeId): int
    {
        $strikeKey = self::CACHE_PREFIX . self::STRIKES_PREFIX . $challengeId;

        $strikes = Cache::increment($strikeKey);

        if ($strikes === 1) {
            Cache::put($strikeKey, 1, self::CHALLENGE_TTL_SECONDS);
        }

        return $strikes;
    }

    public function initiateChallenge(User $user, ChallengeType $type, string $deviceId, Systems $system): AuthenticationOutcome
    {
        $challengeId = (string) Str::ulid();
        $otpCode = null;

        if ($type === ChallengeType::DEVICE_VERIFICATION) {
            $lockKey = "otp_cooldown:{$user->id}";

            if (! Cache::add($lockKey, true, 60)) {
                $this->logger->warning('OTP Generation rate limit triggered.', [
                    'user_id' => $user->id,
                ]);

                throw new InvalidVerificationRequest('Please wait 60 seconds before requesting another code.');
            }

            $otpCode = $this->otpService->generate(6);
        }

        $challenge = new PendingAuthChallenge(
            userId: $user->id,
            challengeId: $challengeId,
            deviceId: $deviceId,
            type: $type,
            system: $system,
            otpCode: $otpCode
        );

        $this->make($challenge);

        if ($type === ChallengeType::DEVICE_VERIFICATION) {
            DeviceVerificationRequested::dispatch($user, $otpCode, $system);
        }

        return AuthenticationOutcome::challenge(
            challengeId: $challengeId,
            challengeType: $type,
            deviceId: $deviceId
        );
    }

    private function make(PendingAuthChallenge $dto): void
    {
        $payload = [
            'user_id' => $dto->userId,
            'device_id' => $dto->deviceId,
            'type' => $dto->type->value,
            'system' => $dto->system->value,
            'fingerprint' => $this->generateFingerprint(),
            'otp_hash' => $dto->otpCode ? $this->otpService->hash($dto->otpCode) : null,

        ];

        Cache::put(
            key: self::CACHE_PREFIX . $dto->challengeId,
            value: $payload,
            ttl: self::CHALLENGE_TTL_SECONDS
        );
    }
}
