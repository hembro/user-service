<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\DTOs\Api\V1\Shared\RequestMetadata;
use App\Events\Auth\SuspiciousSessionDetected;
use App\Services\Auth\DeviceTrustService;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class EnsureDeviceIsTrusted
{
    public function __construct(
        private DeviceTrustService $deviceService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return $next($request);
        }

        $deviceId = $this->deviceService->resolveDeviceId($request);

        if (! $deviceId) {
            throw new AuthenticationException('Device identifier is missing. Please login again.');
        }

        $metadata = RequestMetadata::fromRequest($request);

        if (! $this->deviceService->isTrusted($request->user(), $deviceId, $metadata)) {
            SuspiciousSessionDetected::dispatch($request->user(), $metadata);
            throw new AuthenticationException('Device context mismatch. Please login again.');
        }

        return $next($request);
    }
}
