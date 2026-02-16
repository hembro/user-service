<?php

declare(strict_types=1);

namespace App\Listeners\Auth;

use App\Events\Auth\UserLoggedIn;
use App\Models\UserDevice;
use App\Services\Auth\ChallengeService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;
use Throwable;

final class TouchUserDevice implements ShouldQueue
{
    public int $tries = 3;

    public array $backoff = [10, 30, 60];

    public function __construct(
        private readonly ChallengeService $challengeService,
        private readonly LoggerInterface $logger
    ) {}

    public function handle(UserLoggedIn $event): void
    {
        $metadata = $event->metadata;

        $now = CarbonImmutable::createFromTimestamp($metadata->timestamp);

        try {
            UserDevice::query()->upsert(
                values: [
                    'user_id' => $event->user->id,
                    'device_id' => $event->deviceId,
                    'name' => $metadata->clientType,
                    'fingerprint_hash' => $this->challengeService->generateFingerprint($metadata),
                    'last_ip' => $metadata->ip,
                    'last_used_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                uniqueBy: ['user_id', 'device_id'],
                update: ['last_used_at', 'last_ip', 'fingerprint_hash', 'updated_at']
            );
        } catch (Throwable $e) {
            $this->logger->error('Failed to touch user device', [
                'user_id' => $event->user->id,
                'device' => $event->deviceId,
                'exception' => $e,
            ]);

            throw $e;
        }
    }
}
