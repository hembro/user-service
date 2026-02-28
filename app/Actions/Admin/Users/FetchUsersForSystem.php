<?php

declare(strict_types=1);

namespace App\Actions\Admin\Users;

use App\Commands\Admin\Users\IndexUserCommand;
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

    public function handle(IndexUserCommand $command): LengthAwarePaginator
    {
        if ($this->hasActiveFilters($command)) {
            return $this->query($command)->paginate($command->perPage);
        }

        return Cache::tags(['users_index', "users_index.{$command->system->value}"])
            ->remember(
                key: "users_index.{$command->system->value}.page_{$command->page}",
                ttl: now()->addMinutes(10),
                callback: fn () => $this->query($command)->paginate($command->perPage)
            );
    }

    private function query(IndexUserCommand $command): Builder
    {
        $query = User::query()
            ->with(['profile', 'roles.permissions', 'permissions'])
            ->role(Roles::forSystem($command->system, true));

        return $this->pipeline->send($query)
            ->through([
                new Users\FilterBySearch($command->search),
                new Users\FilterByRole($command->role),
                new Users\FilterByStatus($command->status),
                new Users\Sort($command->sort),
                new Users\FilterByTrashed($command->trashed),
            ])
            ->thenReturn();
    }

    private function hasActiveFilters(IndexUserCommand $command): bool
    {
        return ! empty($command->search) || ! empty($command->role) || ! empty($command->status) || ! empty($command->sort) || ! empty($command->trashed);
    }
}
