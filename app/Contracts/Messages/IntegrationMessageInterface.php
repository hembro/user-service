<?php

declare(strict_types=1);

namespace App\Contracts\Messages;

interface IntegrationMessageInterface
{
    public function getEventId(): string;

    public function toPayload(): array;
}
