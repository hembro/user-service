<?php

declare(strict_types=1);

namespace App\DTOs\Api\V1\Admin\Users;

use App\Enums\Roles;
use App\Enums\Sex;
use App\Enums\Suffix;
use App\Enums\Systems;
use App\Enums\Titles;

final readonly class UpdateUserDTO
{
    public function __construct(
        public string $email,
        public ?Titles $title,
        public string $firstName,
        public ?string $middleName,
        public string $lastName,
        public ?Suffix $suffix,
        public Sex $sex,
        public ?string $mobileNumber,
        public array $preferences,
        public array $roles,
        public Systems $system
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            email: $data['email'],
            title: ! empty($data['title']) ? Titles::from($data['title']) : null,
            firstName: $data['first_name'],
            middleName: $data['middle_name'] ?? null,
            lastName: $data['last_name'],
            suffix: ! empty($data['suffix']) ? Suffix::from($data['suffix']) : null,
            sex: Sex::from($data['sex']),
            mobileNumber: $data['mobile_number'] ?? null,
            preferences: $data['preferences'] ?? [],
            roles: array_map(
                fn (string $role) => Roles::from($role),
                $data['roles']
            ),
            system: Systems::from($data['system'])
        );
    }
}
