<?php

declare(strict_types=1);

namespace App\Exceptions\Auth;

use App\Traits\HasApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class InvalidTwoFactorRequest extends Exception
{
    use HasApiResponse;

    public function render(): JsonResponse
    {
        return $this->error(
            message: $this->getMessage(),
            code: Response::HTTP_FORBIDDEN
        );
    }
}
