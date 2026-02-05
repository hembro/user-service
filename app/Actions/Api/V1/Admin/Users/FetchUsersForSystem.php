<?php

declare(strict_types=1);

namespace App\Actions\Api\V1\Admin\Users;

use App\DTOs\Api\V1\Admin\Users\UserIndexDTO;
use App\Enums\Roles;
use App\Models\User;
use App\QueryFilters\Users;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Cache;

final readonly class FetchUsersForSystem
{
    public function __construct(
        private Pipeline $pipeline
    ) {}

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

        return $this->pipeline->send($query)
            ->through([
                new Users\FilterBySearch($dto->search),
                new Users\FilterByRole($dto->role),
                new Users\FilterByStatus($dto->status),
                new Users\Sort($dto->sort),
            ])
            ->thenReturn();
    }
}
