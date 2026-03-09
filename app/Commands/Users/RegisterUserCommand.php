<?php

declare(strict_types=1);

namespace App\Commands\Users;

use App\Enums\Sex;
use App\Enums\Suffix;
use App\Enums\Titles;
use App\Http\Requests\Api\V1\Users\RegisterRequest;
use jeremyaliparo\Foundation\Enums\System;
use SensitiveParameter;

final readonly class RegisterUserCommand
{
    public function __construct(
        public string $email,
        #[SensitiveParameter]
        public string $password,
        public ?Titles $title,
        public string $firstName,
        public ?string $middleName,
        public string $lastName,
        public ?array $suffixes,
        public Sex $sex,
        public ?string $mobileNumber,
        public array $preferences,
        public string $deviceId,
        public System $system,
        public array $systemContext,
    ) {}

    public static function fromRequest(RegisterRequest $request, string $deviceId): self
    {
        $data = $request->validated();

        return new self(
            email: $data['email'],
            password: $data['password'],
            title: $request->enum('title', Titles::class),
            firstName: $data['first_name'],
            middleName: $data['middle_name'] ?? null,
            lastName: $data['last_name'],
            suffixes: $request->enums('suffixes', Suffix::class),
            sex: $request->enum('sex', Sex::class),
            mobileNumber: $data['mobile_number'] ?? null,
            preferences: $data['preferences'] ?? [],
            deviceId: $deviceId,
            system: $request->attributes->get('system'),
            systemContext: $data['system_context'] ?? [],
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
