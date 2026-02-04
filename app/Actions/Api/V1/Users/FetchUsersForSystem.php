<?php

declare(strict_types=1);

namespace App\Actions\Api\V1\Users;

use App\DTOs\Api\V1\Users\UserIndexDTO;
use App\Enums\Roles;
use App\Models\User;
use App\QueryFilters;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pipeline\Pipeline;
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
        $query = User::query()
            ->with(['profile', 'roles', 'permissions'])
            ->role(Roles::forSystem($dto->system, true));

        return app(Pipeline::class)
            ->send($query)
            ->through([
                new QueryFilters\FilterBySearch($dto->search),
                new QueryFilters\FilterByRole($dto->role),
                new QueryFilters\FilterByStatus($dto->status),
                new QueryFilters\Sort($dto->sort),
            ])
            ->thenReturn();
    }
}
