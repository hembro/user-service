<?php

declare(strict_types=1);

namespace App\DTOs\Api\V1\Users\IntegrationEvents;

use App\Enums\Infrastructure\RoutingKey;
use App\Events\Users\UserRegistered;
use Illuminate\Contracts\Support\Arrayable;

final readonly class UserRegisteredIntegrationEvent implements Arrayable
{
    public function __construct(
        public string $id,
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
        $user = $event->user->loadMissing('profile');

        return new self(
            id: (string) $user->id,
            email: $user->email,
            status: $user->status->value,
            fullName: $user->profile?->full_name,
            title: $user->profile?->title?->value,
            firstName: $user->profile?->first_name,
            middleName: $user->profile?->middle_name,
            lastName: $user->profile?->last_name,
            suffix: $user->profile?->suffix?->value,
            sex: $user->profile?->sex->value,
            mobileNumber: $user->profile?->mobile_number,
            emailVerifiedAt: $user->email_verified_at?->toIso8601String(),
            createdAt: $user->created_at->toIso8601String(),
        );
    }

    public function toArray(): array
    {
        return [
            'event' => RoutingKey::USER_REGISTERED->value,
            'data' => [
                'id' => $this->id,
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
