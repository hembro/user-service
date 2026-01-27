<?php

declare(strict_types=1);

namespace App\DTOs\Api\V1\Users;

final readonly class RegisterUserDTO
{
    public function __construct(
        public string $email,
        public string $password,
        public ?string $title,
        public string $firstName,
        public ?string $middleName,
        public string $lastName,
        public ?string $suffix,
        public string $sex,
        public ?string $mobileNumber,
        public array $preferences,
        public string $system
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            email: $data['email'],
            password: $data['password'],
            title: $data['title'] ?? null,
            firstName: $data['first_name'],
            middleName: $data['middle_name'] ?? null,
            lastName: $data['last_name'],
            suffix: $data['suffix'] ?? null,
            sex: $data['sex'],
            mobileNumber: $data['mobile_number'] ?? null,
            preferences: $data['preferences'] ?? [],
            system: $data['system'],
        );
    }
}
