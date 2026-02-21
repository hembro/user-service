<?php

declare(strict_types=1);

namespace App\Actions\Admin\Users;

use App\Commands\Admin\Users\ShowUserCommand;
use App\Models\User;

final readonly class FetchUserForSystem
{
    public function handle(ShowUserCommand $command): User
    {
        return $command->user->load([
            'profile',

            'roles' => fn ($query) => $query
                ->where('name', 'like', "{$command->system->value}.%")
                ->with('permissions'),

            'permissions' => fn ($query) => $query
                ->where('name', 'like', "{$command->system->value}.%"),
        ]);
    }
}
