<?php

declare(strict_types=1);

namespace App\Messages\Integration\Shared;

use App\DTOs\Messages\ActionRequestData;
use App\DTOs\Messages\Actor;
use App\DTOs\Messages\Target;
use App\Enums\Infrastructure\RoutingKey;

final class ActionRequestedMessage extends AbstractIntegrationMessage
{
    public static function make(RoutingKey $event, Actor $actor, Target $target, MessageMeta $meta, ActionRequestData $request): self
    {
        $data = [
            'actor' => $actor->toArray(),
            'target' => $target->toArray(),
            'context' => [
                'request' => $request->toArray(),
            ],
        ];

        return new self($event, $data, $meta);
    }
}
