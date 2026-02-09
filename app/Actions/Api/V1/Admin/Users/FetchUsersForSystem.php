<?php

declare(strict_types=1);

namespace App\Actions\Api\V1\Admin\Users;

use App\DTOs\Api\V1\Admin\Users\IndexUserDTO;
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

    public function handle(IndexUserDTO $dto): LengthAwarePaginator
    {
        if ($this->hasActiveFilters($dto)) {
            return $this->query($dto)->paginate($dto->perPage);
        }

        return Cache::tags(['users_index', "users_index.{$dto->system->value}"])
            ->remember(
                key: "users_index.{$dto->system->value}.page_{$dto->page}",
                ttl: now()->addMinutes(10),
                callback: fn () => $this->query($dto)->paginate($dto->perPage)
            );
    }

    private function query(IndexUserDTO $dto): Builder
    {
        $query = User::query()
            ->with(['profile', 'roles.permissions', 'permissions'])
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

    private function hasActiveFilters(IndexUserDTO $dto): bool
    {
        return ! empty($dto->search) || ! empty($dto->role) || ! empty($dto->status) || ! empty($dto->sort);
    }
}
