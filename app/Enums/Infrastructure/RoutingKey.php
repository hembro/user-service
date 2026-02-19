<?php

declare(strict_types=1);

namespace App\Enums\Infrastructure;

enum RoutingKey: string
{
    case USER_REGISTERED = 'user.registered';

    public function matches(string $key): bool
    {
        return $this->value === $key;
    }
}
