<?php

declare(strict_types=1);

namespace App\DTOs\Shared;

use Illuminate\Http\Request;

final readonly class RequestMetadata
{
    public function __construct(
        public string $ip,
        public string $userAgent,
        public string $clientType,
        public int $timestamp,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            ip: $request->ip() ?? '127.0.0.1',
            userAgent: mb_substr($request->userAgent() ?? 'unknown', 0, 500),
            clientType: self::detectClientType($request->userAgent() ?? 'unknown'),
            timestamp: now()->timestamp,
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            ip: $data['ip'],
            userAgent: $data['user_agent'],
            clientType: $data['client_type'],
            timestamp: $data['timestamp'],
        );
    }

    public function toArray(): array
    {
        return [
            'ip' => $this->ip,
            'user_agent' => $this->userAgent,
            'client_type' => $this->clientType,
            'timestamp' => $this->timestamp,
        ];
    }

    private static function detectClientType(string $ua): string
    {
        if (preg_match('/(iPhone|iPad|Android)/i', $ua)) {
            return 'mobile_browser';
        }

        return 'desktop_web';
    }
}
