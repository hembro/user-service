<?php

declare(strict_types=1);

namespace App\QueryFilters\Users;

use Closure;
use Illuminate\Database\Eloquent\Builder;

final class FilterBySearch
{
    public function __construct(private ?string $search) {}

    public function handle(Builder $query, Closure $next)
    {
        if (! $this->search) {
            return $next($query);
        }

        $query->where(function (Builder $q) {
            $q->where('email', 'like', "%{$this->search}%")
                ->orWhereHas('profile', function (Builder $profileQuery) {
                    $profileQuery->where('full_name', 'ilike', "%{$this->search}%");
                });
        });

        return $next($query);
    }
}
