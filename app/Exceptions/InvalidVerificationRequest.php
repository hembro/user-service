<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Traits\HasApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class InvalidVerificationRequest extends Exception
{
    use HasApiResponse;

    public function render(Request $request): JsonResponse
    {
        return $this->error(
            message: $this->getMessage(),
            code: Response::HTTP_FORBIDDEN
        );
    }
}
