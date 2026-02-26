<?php

declare(strict_types=1);

namespace App\Messages\Integration\Shared;

use App\DTOs\Messages\Actor;
use App\DTOs\Messages\Target;
use App\Enums\Infrastructure\RoutingKey;

final class ActionOccurredMessage extends AbstractIntegrationMessage
{
    public static function make(
        RoutingKey $event,
        Actor $actor,
        MessageMeta $meta,
        ?Target $target = null,
        array $context = []
    ): self {
        $data = [
            'actor' => $actor->toArray(),
        ];

        if ($target !== null) {
            $data['target'] = $target->toArray();
        }

        if (! empty($context)) {
            $data['context'] = $context;
        }

        return new self($event, $data, $meta);
    }
}
