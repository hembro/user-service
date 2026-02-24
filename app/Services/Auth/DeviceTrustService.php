<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Contracts\Auth\DeviceTrustVerifier;
use App\DTOs\Shared\RequestMetadata;
use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

final readonly class DeviceTrustService implements DeviceTrustVerifier
{
    private const CACHE_TTL_MINUTES = 60;

    public function __construct(
        private ChallengeService $challengeService
    ) {}

    public function isTrusted(User $user, string $deviceId, RequestMetadata $metadata): bool
    {
        if ($deviceId === null) {
            return false;
        }

        $currentFingerprint = $this->challengeService->generateFingerprint($metadata);
        $cacheKey = $this->cacheKey($user->id, $deviceId);
        $cachedHash = Cache::get($cacheKey);

        if ($cachedHash && hash_equals($cachedHash, $currentFingerprint)) {
            return true;
        }

        /** @var UserDevice|null */
        $device = $user->devices()
            ->where('device_id', $deviceId)
            ->whereNotNull('verified_at')
            ->first();

        if (! $device) {
            return false;
        }

        if (! hash_equals($device->fingerprint_hash, $currentFingerprint)) {
            return false;
        }

        Cache::put($cacheKey, $device->fingerprint_hash, now()->addMinutes(self::CACHE_TTL_MINUTES));

        return true;
    }

    public function trustDevice(User $user, string $deviceId, RequestMetadata $metadata): void
    {
        $user->devices()->updateOrCreate(
            attributes: ['device_id' => $deviceId],
            values: [
                'fingerprint_hash' => $this->challengeService->generateFingerprint($metadata),
                'name' => $metadata->clientType . ' on ' . $metadata->userAgent,
                'last_ip' => $metadata->ip,
                'last_used_at' => now(),
                'verified_at' => now(),
            ]
        );
    }

    public function resolveDeviceId(Request $request): ?string
    {
        $headerValue = $request->header('X-Device-UUID');

        if (! blank($headerValue)) {
            return $headerValue;
        }

        return $request->cookie(config('cookie.device_id.name'));
    }

    public function forgetDevice(User $user, string $deviceId): void
    {
        Cache::forget($this->cacheKey($user->id, $deviceId));
        $user->devices()->where('device_id', $deviceId)->delete();
    }

    private function cacheKey(string $userId, string $deviceId): string
    {
        return "auth:trust:{$userId}:{$deviceId}";
    }
}
