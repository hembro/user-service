<?php

declare(strict_types=1);

namespace App\QueryFilters\Users;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use jeremyaliparo\IntegrationSchemas\Enums\Users\UserStatus;

final class FilterByDeletedState
{
    public function __construct(private ?string $option) {}

    public function handle(Builder $query, Closure $next)
    {
        if ($this->option === 'only') {
            $query->where('status', UserStatus::DELETED);
        } elseif ($this->option === 'without' || blank($this->option)) {
            $query->where('status', '!=', UserStatus::DELETED);
        }

        return $next($query);
    }
}
