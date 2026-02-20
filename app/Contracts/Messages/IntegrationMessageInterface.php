<?php

declare(strict_types=1);

namespace App\Contracts\Messages;

interface IntegrationMessageInterface
{
    public function getMessageId(): string;

    public function toPayload(): array;
}
