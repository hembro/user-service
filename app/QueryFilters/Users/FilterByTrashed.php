<?php

declare(strict_types=1);

namespace App\QueryFilters\Users;

use Closure;
use Illuminate\Database\Eloquent\Builder;

final class FilterByTrashed
{
    public function __construct(private ?string $option) {}

    public function handle(Builder $query, Closure $next)
    {
        if ($this->option === 'with') {
            $query->withTrashed();
        } elseif ($this->option === 'only') {
            $query->onlyTrashed();
        } elseif ($this->option === 'without') {
            $query->withoutTrashed();
        }

        return $next($query);
    }
}
