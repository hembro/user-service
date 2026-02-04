<?php

declare(strict_types=1);

namespace App\Actions\Api\V1\Users;

use App\DTOs\Api\V1\Users\UserIndexDTO;
use App\Enums\Roles;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

final class FetchUsersForSystem
{
    public function handle(UserIndexDTO $dto): LengthAwarePaginator
    {
        return Cache::tags(['users_index', "users_index.{$dto->system->value}"])
            ->remember(
                key: $dto->generateCacheKey(),
                ttl: now()->addHour(1),
                callback: fn () => $this->query($dto)->paginate($dto->perPage)
            );
    }

    private function query(UserIndexDTO $dto): Builder
    {
        return User::query()
            ->with(['profile', 'roles', 'permissions'])

            // A. System Scope
            ->role(Roles::forSystem($dto->system, true))

            // B. Search
            ->when(
                $dto->search,
                fn (Builder $query, string $search) => $query->where(function (Builder $q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })
            )

            // C. Role Filter
            ->when(
                $dto->role,
                fn (Builder $query, string $role) => $query->role($role)
            )

            // D. Sorting
            ->latest();
    }
}
