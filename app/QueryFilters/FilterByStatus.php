<?php

declare(strict_types=1);

namespace App\QueryFilters;

use Closure;
use Illuminate\Database\Eloquent\Builder;

final class FilterByStatus
{
    public function __construct(private ?string $status) {}

    public function handle(Builder $query, Closure $next)
    {
        if (! $this->status) {
            return $next($query);
        }

        $query->where('status', $this->status);

        return $next($query);
    }
}
