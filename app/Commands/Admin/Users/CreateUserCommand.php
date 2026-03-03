<?php

declare(strict_types=1);

namespace App\Commands\Admin\Users;

use App\Enums\Roles;
use App\Enums\Sex;
use App\Enums\Suffix;
use App\Enums\Systems;
use App\Enums\Titles;
use App\Http\Requests\Api\V1\Admin\Users\StoreRequest;
use App\Models\User;
use SensitiveParameter;

final readonly class CreateUserCommand
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
        public array $roles,
        public User $actor,
        public ?string $reason,
        public Systems $system,
        public ?array $systemContext
    ) {}

    public static function fromRequest(StoreRequest $request): self
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
            roles: array_map(
                fn (string $role) => Roles::from($role),
                $data['roles']
            ),
            actor: $request->user(),
            reason: $data['reason'] ?? null,
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
