<?php

declare(strict_types=1);

namespace App\Listeners\Auth;

use App\Events\Auth\UserLoggedIn;
use App\Models\UserDevice;
use App\Services\Auth\ChallengeService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Context;
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
        $now = now();

        try {
            UserDevice::query()->upsert(
                values: [
                    'user_id' => $event->user->id,
                    'device_id' => $event->deviceId,
                    'name' => Context::get('client_type', 'unknown') . ' on ' . Context::get('user_agent', 'unknown'),
                    'fingerprint_hash' => $this->challengeService->generateFingerprint(),
                    'last_ip' => Context::get('ip_address'),
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
