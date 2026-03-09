<?php

declare(strict_types=1);

namespace App\Commands\Users;

use App\Enums\Sex;
use App\Enums\Suffix;
use App\Enums\Titles;
use App\Http\Requests\Api\V1\Users\UpdateProfileRequest;
use App\Models\User;
use jeremyaliparo\Foundation\Enums\System;

final readonly class UpdateProfileCommand
{
    public function __construct(
        public User $user,
        public ?Titles $title,
        public string $firstName,
        public ?string $middleName,
        public string $lastName,
        public ?array $suffixes,
        public Sex $sex,
        public ?string $mobileNumber,
        public array $preferences,
        public System $system,
        public ?array $systemContext
    ) {}

    public static function fromRequest(UpdateProfileRequest $request, User $user): self
    {
        $data = $request->validated();

        return new self(
            user: $user,
            title: $request->enum('title', Titles::class),
            firstName: $data['first_name'],
            middleName: $data['middle_name'] ?? null,
            lastName: $data['last_name'],
            suffixes: $request->enums('suffixes', Suffix::class),
            sex: $request->enum('sex', Sex::class),
            mobileNumber: $data['mobile_number'] ?? null,
            preferences: $data['preferences'] ?? [],
            system: $request->attributes->get('system'),
            systemContext: $data['system_context'] ?? []
        );
    }

    public function toProfileAttributes(): array
    {
        return [
            'title' => $this->title,
            'first_name' => $this->firstName,
            'middle_name' => $this->middleName,
            'last_name' => $this->lastName,
            'suffixes' => $this->suffixes,
            'sex' => $this->sex,
            'mobile_number' => $this->mobileNumber,
            'preferences' => $this->preferences,
        ];
    }
}
