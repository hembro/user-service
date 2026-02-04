<?php

declare(strict_types=1);

namespace App\QueryFilters\Users;

use Closure;
use Illuminate\Database\Eloquent\Builder;

final class FilterByRole
{
    public function __construct(private ?string $role) {}

    public function handle(Builder $query, Closure $next)
    {
        if (! $this->role) {
            return $next($query);
        }

        $query->role($this->role);

        return $next($query);
    }
}
