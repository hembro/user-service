<?php

declare(strict_types=1);

namespace App\DTOs\Api\V1\Users\IntegrationEvents;

use App\Enums\Infrastructure\RoutingKey;
use App\Events\Admin\UserPasswordReset;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\Support\Arrayable;

final readonly class UserPasswordResetIntegrationEvent implements Arrayable
{
    public function __construct(
        public string $userId,
        public string $userEmail,
        public string $actorId,
        public string $actorType,
        public ?string $actorName,
        public string $originSystem,
        public string $occurredAt,
    ) {}

    /**
     * Factory 1: The user reset their own password via the "Forgot Password" flow.
     */
    public static function fromUserReset(PasswordReset $event): self
    {
        return new self(
            userId: (string) $event->user->id,
            userEmail: $event->user->email,
            actorId: (string) $event->user->id,
            actorType: 'user',
            actorName: null,
            originSystem: request()->attributes->get('system')->value,
            occurredAt: now()->toIso8601String(),
        );
    }

    /**
     * Factory 2: An admin reset the user's password.
     */
    public static function fromAdminReset(UserPasswordReset $event): self
    {
        $event->admin->loadMissing('profile');

        return new self(
            userId: (string) $event->user->id,
            userEmail: $event->user->email,
            actorId: (string) $event->admin->id,
            actorType: 'admin',
            actorName: $event->admin->profile?->full_name,
            originSystem: $event->system->value,
            occurredAt: now()->toIso8601String(),
        );
    }

    public function toArray(): array
    {
        return [
            'event' => RoutingKey::USER_PASSWORD_RESET->value,
            'data' => [
                'user' => [
                    'id' => $this->userId,
                    'email' => $this->userEmail,
                ],
                'actor' => [
                    'id' => $this->actorId,
                    'type' => $this->actorType,
                    'name' => $this->actorName,
                ],
                'origin_system' => $this->originSystem,
            ],
            'meta' => [
                'timestamp' => $this->occurredAt,
                'source' => config('app.name'),
                'version' => '1.0',
            ],
        ];
    }
}
