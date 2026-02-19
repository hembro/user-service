<?php

declare(strict_types=1);

namespace App\DTOs\Api\V1\Users\IntegrationEvents;

use App\Enums\Infrastructure\RoutingKey;
use App\Events\Users\UserRegistered;
use Illuminate\Contracts\Support\Arrayable;

final readonly class UserRegisteredIntegrationEvent implements Arrayable
{
    public function __construct(
        public string $userId,
        public string $email,
        public string $status,
        public ?string $fullName,
        public ?string $title,
        public ?string $firstName,
        public ?string $middleName,
        public ?string $lastName,
        public ?string $suffix,
        public string $sex,
        public ?string $mobileNumber,
        public ?string $emailVerifiedAt,
        public string $createdAt,
    ) {}

    public static function fromDomainEvent(UserRegistered $event): self
    {
        $event->user->loadMissing('profile');

        return new self(
            userId: (string) $event->user->id,
            email: $event->user->email,
            status: $event->user->status->value,
            fullName: $event->user->profile?->full_name,
            title: $event->user->profile?->title?->value,
            firstName: $event->user->profile?->first_name,
            middleName: $event->user->profile?->middle_name,
            lastName: $event->user->profile?->last_name,
            suffix: $event->user->profile?->suffix?->value,
            sex: $event->user->profile?->sex->value,
            mobileNumber: $event->user->profile?->mobile_number,
            emailVerifiedAt: $event->user->email_verified_at?->toIso8601String(),
            createdAt: $event->user->created_at->toIso8601String(),
        );
    }

    public function toArray(): array
    {
        return [
            'event' => RoutingKey::USER_REGISTERED->value,
            'data' => [
                'id' => $this->userId,
                'email' => $this->email,
                'status' => $this->status,
                'full_name' => $this->fullName,
                'title' => $this->title,
                'first_name' => $this->firstName,
                'middle_name' => $this->middleName,
                'last_name' => $this->lastName,
                'suffix' => $this->suffix,
                'mobile_number' => $this->mobileNumber,
                'email_verified_at' => $this->emailVerifiedAt,
            ],
            'meta' => [
                'timestamp' => $this->createdAt,
                'source' => config('app.name'),
                'version' => '1.0',
            ],
        ];
    }
}
