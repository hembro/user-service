<?php

declare(strict_types=1);

namespace App\Commands\Admin\Users;

use App\Enums\Roles;
use App\Enums\Systems;
use App\Http\Requests\Api\V1\Admin\Users\UpdateRoleRequest;
use App\Models\User;

final readonly class UpdateRoleCommand
{
    public function __construct(
        public User $targetUser,
        public User $actor,
        public array $roles,
        public Systems $system
    ) {}

    public static function fromRequest(UpdateRoleRequest $request, User $targetUser): self
    {
        $data = $request->validated();

        return new self(
            targetUser: $targetUser,
            actor: $request->user(),
            roles: array_map(
                fn (string $role) => Roles::from($role),
                $data['roles']
            ),
            system: $request->attributes->get('system')
        );
    }
}
