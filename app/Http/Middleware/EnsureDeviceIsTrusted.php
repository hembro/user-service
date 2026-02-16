<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Contracts\Auth\DeviceTrustVerifier;
use App\DTOs\Api\V1\Shared\RequestMetadata;
use App\Events\Auth\SuspiciousSessionDetected;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class EnsureDeviceIsTrusted
{
    public function __construct(
        private DeviceTrustVerifier $trustVerifier
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return $next($request);
        }

        $metadata = RequestMetadata::fromRequest($request);

        $deviceId = $request->header('X-Device-UUID') ?? $request->cookie(config('cookie.device_id.name'));

        if (! $deviceId) {
            throw new AuthenticationException('Device identifier missing.');
        }

        if (! $this->trustVerifier->isTrusted($request->user(), $deviceId, $metadata)) {
            SuspiciousSessionDetected::dispatch($request->user(), $metadata);
            throw new AuthenticationException('Device context mismatch. Please login again.');
        }

        return $next($request);
    }
}
