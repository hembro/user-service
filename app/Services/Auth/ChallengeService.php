<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\DTOs\Api\V1\Shared\RequestMetadata;
use App\Enums\Auth\ChallengeType;
use App\Models\User;
use App\Notifications\VerifyDeviceLogin;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

final readonly class ChallengeService
{
    private const CHALLENGE_TTL = 300; // 5 Minutes

    private const CACHE_PREFIX = 'auth:challenge:';

    public function dispatch(User $user, ChallengeType $type, RequestMetadata $metadata): string
    {
        $challengeId = (string) Str::uuid();
        $otpCode = (string) random_int(100000, 999999);

        Cache::put(
            key: self::CACHE_PREFIX . $challengeId,
            value: [
                'user_id' => $user->id,
                'type' => $type->value,
                'otp_hash' => hash('sha256', $otpCode),
                'fingerprint' => $this->generateFingerprint($metadata),
                'device_uuid' => $metadata->deviceId ?? (string) Str::uuid(),
            ],
            ttl: self::CHALLENGE_TTL
        );

        match ($type) {
            ChallengeType::DEVICE_VERIFICATION => $user->notify(
                new VerifyDeviceLogin($otpCode, $metadata->userAgent)
            ),
        };

        return $challengeId;
    }

    /**
     * Retrieve payload for verification.
     */
    public function retrieve(string $challengeId): ?array
    {
        return Cache::get(self::CACHE_PREFIX . $challengeId);
    }

    public function forget(string $challengeId): void
    {
        Cache::forget(self::CACHE_PREFIX . $challengeId);
    }

    /**
     * Helper to generate fingerprint for consistency check.
     * (Duplicated logic from DeviceTrustService is acceptable here to keep services decoupled,
     * OR extract to a shared helper/trait if strict DRY is preferred).
     */
    public function generateFingerprint(RequestMetadata $metadata): string
    {
        return hash_hmac(
            'sha256',
            $metadata->userAgent . '|' . $metadata->clientType,
            config('app.key')
        );
    }
}
