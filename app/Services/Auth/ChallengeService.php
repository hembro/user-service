<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\DTOs\Auth\AuthenticationOutcome;
use App\DTOs\Auth\PendingAuthChallenge;
use App\DTOs\Shared\RequestMetadata;
use App\Enums\Auth\ChallengeType;
use App\Enums\Systems;
use App\Events\Auth\DeviceVerificationRequested;
use App\Exceptions\Auth\InvalidVerificationRequest;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
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

    public function generateFingerprint(RequestMetadata $metadata): string
    {
        return hash_hmac(
            'sha256',
            $metadata->userAgent . '|' . $metadata->clientType,
            config('app.key')
        );
    }

    public function validFingerprint(string $fingerprint, RequestMetadata $metadata): bool
    {
        return hash_equals($fingerprint, $this->generateFingerprint($metadata));
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

    public function initiateChallenge(User $user, ChallengeType $type, string $deviceId, Systems $system, RequestMetadata $metadata): AuthenticationOutcome
    {
        $challengeId = (string) Str::ulid();
        $otpCode = null;

        if ($type === ChallengeType::DEVICE_VERIFICATION) {
            $lockKey = "otp_cooldown:{$user->id}";

            if (! Cache::add($lockKey, true, 60)) {
                $this->logger->warning('OTP Generation rate limit triggered.', [
                    'user_id' => $user->id,
                    'ip' => $metadata->ip,
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
            metadata: $metadata,
            otpCode: $otpCode
        );

        $this->make($challenge);

        if ($type === ChallengeType::DEVICE_VERIFICATION) {
            DeviceVerificationRequested::dispatch($user, $otpCode, $system, $metadata);
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
            'fingerprint' => $this->generateFingerprint($dto->metadata),
            'metadata' => $dto->metadata->toArray(),
            'otp_hash' => $dto->otpCode ? $this->otpService->hash($dto->otpCode) : null,

        ];

        Cache::put(
            key: self::CACHE_PREFIX . $dto->challengeId,
            value: $payload,
            ttl: self::CHALLENGE_TTL_SECONDS
        );
    }
}
