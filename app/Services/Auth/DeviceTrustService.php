<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\DTOs\Api\V1\Shared\RequestMetadata;
use App\Enums\Auth\ChallengeType;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

final readonly class DeviceTrustService
{
    private const CHALLENGE_TTL_SECONDS = 300; // 5 Minutes

    private const CACHE_PREFIX = 'auth:challenge:';

    public function isTrusted(User $user, RequestMetadata $metadata): bool
    {
        // 1. If no device ID is provided by client, it's automatically untrusted.
        if ($metadata->deviceId === null) {
            return false;
        }

        // 2. Check strict existence in DB for this user.
        // We use the fingerprint to ensure the device config hasn't drastically changed (e.g., session hijacking)
        $device = $user->devices()
            ->where('device_uuid', $metadata->deviceId)
            ->whereNotNull('verified_at')
            ->first();

        if (! $device) {
            return false;
        }

        $currentFingerprint = $this->generateFingerprint($metadata);

        return hash_equals($device->fingerprint_hash, $currentFingerprint);
    }

    public function storeChallengeContext(
        User $user,
        string $challengeId,
        string $otpCode,
        RequestMetadata $metadata
    ): void {
        $payload = [
            'user_id' => $user->id,
            'type' => ChallengeType::DEVICE_VERIFICATION->value,
            'otp_hash' => hash('sha256', $otpCode),
            'fingerprint' => $this->generateFingerprint($metadata),
            'device_uuid' => $metadata->deviceId ?? (string) Str::uuid(),
            'metadata' => serialize($metadata),
        ];

        Cache::put(
            key: self::CACHE_PREFIX . $challengeId,
            value: $payload,
            ttl: self::CHALLENGE_TTL_SECONDS
        );
    }

    /**
     * Deterministic fingerprint generation.
     * Note: IP addresses change on mobile. We often exclude IP from long-term device trust
     * or use subnet only. For this strict example, we rely heavily on UserAgent + ClientType.
     */
    public function generateFingerprint(RequestMetadata $metadata): string
    {
        $data = implode('|', [
            $metadata->userAgent,
            $metadata->clientType,
        ]);

        return hash_hmac('sha256', $data, config('app.key'));
    }

    public function authorizeDevice(User $user, string $deviceUuid, RequestMetadata $metadata): void
    {
        $user->devices()->updateOrCreate(
            ['device_uuid' => $deviceUuid],
            [
                'fingerprint_hash' => $this->generateFingerprint($metadata),
                'name' => $metadata->clientType . ' on ' . $metadata->userAgent,
                'last_ip' => $metadata->ip,
                'last_used_at' => now(),
                'verified_at' => now(),
            ]
        );
    }
}
