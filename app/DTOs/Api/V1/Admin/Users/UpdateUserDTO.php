<?php

declare(strict_types=1);

namespace App\DTOs\Api\V1\Admin\Users;

use App\Enums\Sex;
use App\Enums\Suffix;
use App\Enums\Systems;
use App\Enums\Titles;
use App\Http\Requests\Api\V1\Admin\Users\UpdateRequest;

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
        public Systems $system
    ) {}

    public static function fromRequest(UpdateRequest $request): self
    {
        $data = $request->validated();

        return new self(
            email: $data['email'],
            title: isset($data['title']) ? Titles::from($data['title']) : null,
            firstName: $data['first_name'],
            middleName: $data['middle_name'] ?? null,
            lastName: $data['last_name'],
            suffix: isset($data['suffix']) ? Suffix::from($data['suffix']) : null,
            sex: Sex::from($data['sex']),
            mobileNumber: $data['mobile_number'] ?? null,
            preferences: $data['preferences'] ?? [],
            system: $request->attributes->get('system'),
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            email: $data['email'],
            title: isset($data['title']) ? Titles::from($data['title']) : null,
            firstName: $data['first_name'],
            middleName: $data['middle_name'] ?? null,
            lastName: $data['last_name'],
            suffix: isset($data['suffix']) ? Suffix::from($data['suffix']) : null,
            sex: Sex::from($data['sex']),
            mobileNumber: $data['mobile_number'] ?? null,
            preferences: $data['preferences'] ?? [],
            system: Systems::from($data['system'])
        );
    }

    public function toProfileAttributes(): array
    {
        return [
            'title' => $this->title,
            'first_name' => $this->firstName,
            'middle_name' => $this->middleName,
            'last_name' => $this->lastName,
            'suffix' => $this->suffix,
            'sex' => $this->sex,
            'mobile_number' => $this->mobileNumber,
            'preferences' => $this->preferences,
        ];
    }
}
