<?php

declare(strict_types=1);

namespace App\Listeners\Logs\Auth;

use App\Events\Auth\DeviceUsed;
use App\Models\UserDevice;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Throwable;

final class LogDeviceUsage implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;

    public $backoff = [10, 30, 60];

    public function handle(DeviceUsed $event): void
    {
        $metadata = $event->metadata;
        $occuredAt = CarbonImmutable::createFromTimestamp($metadata->timestamp);

        Log::channel('auth')->info(
            message: 'device usage detected',
            context: [
                'user_id' => $event->user->id,
                'device_uuid' => $metadata->deviceId,
                'ip' => $metadata->ip,
                'timestamp' => $occuredAt->toIso8601String(),
            ]
        );

        if (! $metadata->deviceId) {
            return;
        }

        try {
            UserDevice::query()->upsert(
                values: [
                    'user_id' => $event->user->id,
                    'device_uuid' => $metadata->deviceId,
                    'fingerprint_hash' => $this->generateFingerprint($metadata),
                    'name' => $metadata->clientType,
                    'last_ip' => $metadata->ip,
                    'last_used_at' => $occuredAt,
                ],
                uniqueBy: ['user_id', 'device_uuid'],
                update: ['last_used_at', 'last_ip', 'fingerprint_hash']
            );
        } catch (Throwable $e) {
            Log::error('Failed to update device usage heartbeat', [
                'error' => $e->getMessage(),
                'user_id' => $event->user->id,
            ]);
            throw $e;
        }
    }

    private function generateFingerprint($metadata): string
    {
        return hash_hmac('sha256', $metadata->userAgent . '|' . $metadata->clientType, config('app.key'));
    }
}
