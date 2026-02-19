<?php

declare(strict_types=1);

namespace App\Enums\Infrastructure;

enum OutboxStatus: string
{
    case PENDING = 'pending';
    case PUBLISHED = 'published';
    case FAILED = 'failed';
}
