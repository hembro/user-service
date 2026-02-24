<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\Systems;
use App\Exceptions\Auth\InvalidSystemHeaderException;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

final class CaptureRequestContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $traceId = $request->header('X-Trace-ID', (string) Str::ulid());

        $systemValue = $request->header('X-Source-System');
        $systemString = is_array($systemValue) ? $systemValue[0] : $systemValue;

        if (blank($systemString) || ! $system = Systems::tryFrom((string) $systemString)) {
            throw new InvalidSystemHeaderException('Invalid system header');
        }

        Context::add([
            'trace_id' => $traceId,
            'source_system' => $system->value,
            'user_ip' => $request->ip(),
        ]);

        $request->attributes->set('system', $system);

        $response = $next($request);
        $response->headers->set('X-Trace-ID', $traceId);

        return $response;
    }
}
