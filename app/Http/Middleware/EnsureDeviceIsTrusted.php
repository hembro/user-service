<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Contracts\Auth\DeviceTrustVerifier;
use App\DTOs\Shared\RequestMetadata;
use App\Events\Auth\SuspiciousSessionDetected;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class EnsureDeviceIsTrusted
{
    public function __construct(
        private DeviceTrustVerifier $deviceService,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        $deviceId = $this->deviceService->resolveDeviceId($request);

        if (! $deviceId) {
            throw new AuthenticationException('Device identifier is missing. Please login again.');
        }

        $metadata = RequestMetadata::fromRequest($request);

        if (! $this->deviceService->isTrusted($user, $deviceId, $metadata)) {

            $system = $request->attributes->get('system');

            SuspiciousSessionDetected::dispatch(
                $user,
                'untrusted_device_detected',
                $system,
                $metadata
            );

            throw new AuthenticationException('Device context mismatch. Please login again.');
        }

        return $next($request);
    }
}
