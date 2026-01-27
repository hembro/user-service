<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

trait HasApiResponse
{
    protected function success(mixed $data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        return new JsonResponse(
            data: [
                'success' => true,
                'code' => $code,
                'message' => $message,
                'data' => $data,
            ],
            status: $code
        );
    }

    protected function error(string $message = 'Error', int $code = 400, mixed $errors = null): JsonResponse
    {
        return new JsonResponse(
            data: [
                'success' => false,
                'code' => $code,
                'message' => $message,
                'errors' => $errors,
            ],
            status: $code
        );
    }

    protected function noContent(): JsonResponse
    {
        return new JsonResponse(
            data: [
                'success' => true,
                'code' => Response::HTTP_NO_CONTENT,
            ],
            status: Response::HTTP_NO_CONTENT
        );
    }
}
