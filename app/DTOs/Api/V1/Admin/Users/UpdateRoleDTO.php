<?php

declare(strict_types=1);

namespace App\DTOs\Api\V1\Admin\Users;

use App\Enums\Roles;
use App\Enums\Systems;
use App\Http\Requests\Api\V1\Admin\Users\UpdateRoleRequest;

final readonly class UpdateRoleDTO
{
    public function __construct(
        public array $roles,
        public Systems $system
    ) {}

    public static function fromRequest(UpdateRoleRequest $request): self
    {
        $data = $request->validated();

        return new self(
            roles: array_map(
                fn (string $role) => Roles::from($role),
                $data['roles']
            ),
            system: $request->attributes->get('system'),
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            roles: array_map(
                fn (string $role) => Roles::from($role),
                $data['roles']
            ),
            system: Systems::from($data['system'])
        );
    }
}
