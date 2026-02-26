<?php

declare(strict_types=1);

namespace App\Messages\Integration\Shared;

use App\Contracts\Messages\IntegrationMessageInterface;
use App\Enums\Infrastructure\RoutingKey;
use Illuminate\Support\Str;

abstract class AbstractIntegrationMessage implements IntegrationMessageInterface
{
    protected string $messageId;

    public function __construct(
        protected RoutingKey $event,
        protected array $data,
        protected MessageMeta $meta,
    ) {
        $this->messageId = (string) Str::ulid();
    }

    public function getMessageId(): string
    {
        return $this->messageId;
    }

    public function toPayload(): array
    {
        return array_filter([
            'message_id' => $this->messageId,
            'event' => $this->event->value,
            'data' => $this->data,
            'meta' => $this->meta->toArray(),
        ]);
    }
}
