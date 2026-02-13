<?php

declare(strict_types=1);

namespace App\DTOs\Api\V1\Shared;

use Illuminate\Http\Request;

final readonly class RequestMetadata
{
    public function __construct(
        public string $ip,
        public string $userAgent,
        public string $clientType,
        public int $timestamp,
        public ?string $deviceId,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            ip: $request->ip() ?? '127.0.0.1',
            userAgent: mb_substr($request->userAgent() ?? 'unknown', 0, 500),
            clientType: self::detectClientType($request->userAgent() ?? 'unknown'),
            timestamp: now()->timestamp,
            deviceId: $request->header('X-Device-Id') ?? $request->cookie('device_id'),
        );
    }

    private static function detectClientType(string $ua): string
    {
        if (preg_match('/(iPhone|iPad|Android)/i', $ua)) {
            return 'mobile_browser';
        }

        return 'desktop_web';
    }
}
