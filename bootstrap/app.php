<?php

declare(strict_types=1);

use App\Http\Middleware\CaptureRequestContext;
use App\Http\Middleware\EnsureDeviceIsTrusted;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(
            append: [
                CaptureRequestContext::class,
                EnsureDeviceIsTrusted::class,
            ]
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return new JsonResponse(
                    data: [
                        'success' => false,
                        'message' => $e->getMessage(),
                        'code' => Response::HTTP_UNAUTHORIZED,
                    ],
                    status: Response::HTTP_UNAUTHORIZED
                );
            }

            return null;
        });

        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return new JsonResponse(
                    data: [
                        'success' => false,
                        'message' => $e->validator->errors()->first(),
                        'code' => 422,
                        'errors' => $e->validator->errors(),
                    ],
                    status: Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }

            return null;
        });
    })->create();
