<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Contracts\Auth\DeviceTrustVerifier;
use App\DTOs\Api\V1\Shared\RequestMetadata;
use App\Events\Auth\SuspiciousSessionDetected;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class EnsureDeviceIsTrusted
{
    public function __construct(
        private DeviceTrustVerifier $deviceService,
        private DatabaseManager $db
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

            $this->db->transaction(
                callback: function () use ($user, $system, $metadata): void {
                    SuspiciousSessionDetected::dispatch($user, $system, $metadata);
                }
            );

            throw new AuthenticationException('Device context mismatch. Please login again.');
        }

        return $next($request);
    }
}
