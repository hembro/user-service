<?php

declare(strict_types=1);

namespace App\DTOs\Api\V1\Users;

use App\Enums\Roles;
use App\Enums\Sex;
use App\Enums\Suffix;
use App\Enums\Systems;
use App\Enums\Titles;

final readonly class CreateUserDTO
{
    public function __construct(
        public string $email,
        public string $password,
        public ?Titles $title,
        public string $firstName,
        public ?string $middleName,
        public string $lastName,
        public ?Suffix $suffix,
        public Sex $sex,
        public ?string $mobileNumber,
        public array $preferences,
        public Roles $role,
        public Systems $system
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            email: $data['email'],
            password: $data['password'],
            title: ! empty($data['title']) ? Titles::from($data['title']) : null,
            firstName: $data['first_name'],
            middleName: $data['middle_name'] ?? null,
            lastName: $data['last_name'],
            suffix: ! empty($data['suffix']) ? Suffix::from($data['suffix']) : null,
            sex: Sex::from($data['sex']),
            mobileNumber: $data['mobile_number'] ?? null,
            preferences: $data['preferences'] ?? [],
            role: Roles::from($data['role']),
            system: Systems::from($data['system'])
        );
    }
}
