<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Api\V1\Auth\ResendVerifyEmail;
use App\Actions\Api\V1\Auth\VerifyEmail;
use App\DTOs\Api\V1\Auth\ResendVerifyEmailData;
use App\DTOs\Api\V1\Auth\VerifyEmailData;
use App\Http\Requests\Api\V1\Auth\ResendVerifyEmailRequest;
use App\Traits\HasApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class VerifyEmailController
{
    use HasApiResponse;

    public function __construct(
        private readonly VerifyEmail $verify,
        private readonly ResendVerifyEmail $resend
    ) {}

    public function verify(Request $request, string $id, string $hash): JsonResponse
    {
        $this->verify->handle(
            dto: VerifyEmailData::fromRequest($request, $id, $hash)
        );

        return $this->success(
            message: 'Email verified successfully.'
        );
    }

    public function resend(ResendVerifyEmailRequest $request): JsonResponse
    {
        $this->resend->handle(
            dto: ResendVerifyEmailData::fromRequest($request)
        );

        return $this->success(
            code: Response::HTTP_ACCEPTED
        );
    }
}
