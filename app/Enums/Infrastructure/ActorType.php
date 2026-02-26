<?php

declare(strict_types=1);

namespace App\Enums\Infrastructure;

enum ActorType: string
{
    // A human being operating a client interface (Web/Mobile)
    case USER = 'user';
    case GUEST = 'guest';

    // An automated internal process (Cron job, queue worker, DB trigger)
    case SYSTEM = 'system';

    // A 3rd party integration using a programmatic key
    case API_KEY = 'api_key';

    // Another internal microservice initiating an action
    case MICROSERVICE = 'microservice';
}
