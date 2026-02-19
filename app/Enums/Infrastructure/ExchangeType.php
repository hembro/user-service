<?php

declare(strict_types=1);

namespace App\Enums\Infrastructure;

enum ExchangeType: string
{
    case TOPIC = 'microservices.topic';
}
