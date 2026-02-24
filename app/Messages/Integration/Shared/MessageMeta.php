<?php

declare(strict_types=1);

namespace App\Messages\Integration\Shared;

use App\Enums\Systems;

final readonly class MessageMeta
{
    public static function generate(Systems $originSystem, ?string $timestamp = null): array
    {
        return [
            'timestamp' => $timestamp ?? now()->toIso8601String(),
            'source' => config('app.name'),
            'origin_system' => $originSystem->value,
            'version' => '1.0',
        ];
    }
}
