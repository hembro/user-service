<?php

declare(strict_types=1);

namespace App\Actions\Api\V1\Admin\Users;

use App\DTOs\Api\V1\Admin\Users\ShowUserDTO;
use App\Models\User;

final readonly class FetchUser
{
    public function handle(ShowUserDTO $dto, User $user): User
    {
        return $user->load([
            'profile',
            'roles' => fn ($query) => $query->where('name', 'like', "{$dto->system->value}.%"),
            'permissions' => fn ($query) => $query->where('name', 'like', "{$dto->system->value}.%"),
        ]);
    }
}
