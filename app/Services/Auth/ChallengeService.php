<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\DTOs\Auth\PendingAuthChallenge;
use App\DTOs\Shared\RequestMetadata;
use Illuminate\Support\Facades\Cache;

final readonly class ChallengeService
{
    private const CHALLENGE_TTL_SECONDS = 300;

    private const CACHE_PREFIX = 'auth:challenge:';

    public function __construct(
        private OtpService $otpService
    ) {}

    public function make(PendingAuthChallenge $dto): void
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

    public function retrieve(string $challengeId): ?array
    {
        $data = Cache::get(self::CACHE_PREFIX . $challengeId);

        return is_array($data) ? $data : null;
    }

    public function forget(string $challengeId): void
    {
        Cache::forget(self::CACHE_PREFIX . $challengeId);
    }

    /**
     * Generates a fingerprint to bind the challenge to a specific browser.
     * This prevents a hacker from intercepting the link and opening it on their machine.
     */
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
        $strikeKey = self::CACHE_PREFIX . 'strikes:' . $challengeId;

        $strikes = Cache::increment($strikeKey);

        if ($strikes === 1) {
            Cache::put($strikeKey, 1, self::CHALLENGE_TTL_SECONDS);
        }

        return $strikes;
    }
}
