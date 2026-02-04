<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\Systems;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

final class CaptureRequestContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $traceId = $request->header('X-Trace-ID', Str::uuid()->toString());

        $system = Systems::tryFrom($request->header('X-Source-System', ''));

        Context::add([
            'trace_id' => $traceId,
            'source_system' => $system->value,
            'user_ip' => $request->ip(),
        ]);

        $response = $next($request);
        $response->headers->set('X-Trace-ID', $traceId);

        return $response;
    }
}
