<?php

declare(strict_types=1);

namespace App\Exceptions\Auth;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class InvalidTwoFactorRequest extends Exception
{
    public function render(Request $request): JsonResponse
    {
        return JsonResponse::error(
            message: $this->getMessage(),
            code: Response::HTTP_FORBIDDEN
        );
    }
}
