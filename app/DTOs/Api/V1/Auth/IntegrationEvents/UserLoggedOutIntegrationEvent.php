<?php

declare(strict_types=1);

namespace App\DTOs\Api\V1\Auth\IntegrationEvents;

use App\Enums\Infrastructure\RoutingKey;
use App\Events\Auth\UserLoggedOut;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;

final readonly class UserLoggedOutIntegrationEvent implements Arrayable
{
    public function __construct(
        public string $userId,
        public string $ipAddress,
        public string $userAgent,
        public string $originSystem,
        public string $occurredAt,
    ) {}

    public static function fromDomainEvent(UserLoggedOut $event): self
    {
        return new self(
            userId: (string) $event->user->id,
            ipAddress: $event->metadata->ip ?? 'unknown',
            userAgent: $event->metadata->userAgent ?? 'unknown',
            originSystem: request()->attributes->get('system')->value,
            occurredAt: Carbon::createFromTimestamp($event->metadata->timestamp)->toIso8601String(),
        );
    }

    public function toArray(): array
    {
        return [
            'event' => RoutingKey::USER_LOGGED_OUT->value,
            'data' => [
                'user' => [
                    'id' => $this->userId,
                ],
                'session' => [
                    'ip_address' => $this->ipAddress,
                    'user_agent' => $this->userAgent,
                ],
            ],
            'meta' => [
                'timestamp' => $this->occurredAt,
                'source' => config('app.name'),
                'version' => '1.0',
            ],
        ];
    }
}
