<?php

declare(strict_types=1);

namespace App\QueryFilters;

use Closure;
use Illuminate\Database\Eloquent\Builder;

final class Sort
{
    private const ALLOWED_SORTS = [
        'full_name',
        'created_at',
    ];

    public function __construct(private ?string $sortField) {}

    public function handle(Builder $query, Closure $next)
    {
        if (! $this->sortField) {
            return $next($query->latest());
        }

        $direction = 'asc';
        $column = $this->sortField;

        if (str_starts_with($this->sortField, '-')) {
            $direction = 'desc';
            $column = mb_substr($this->sortField, 1);
        }

        if (! in_array($column, self::ALLOWED_SORTS)) {
            return $next($query->latest());
        }

        if ($column === 'full_name') {
            $query->select('users.*')
                ->join('user_profiles', 'users.id', '=', 'user_profiles.user_id')
                ->orderBy('user_profiles.full_name', $direction);

            return $next($query);
        }

        $query->orderBy($column, $direction);

        return $next($query);
    }
}
