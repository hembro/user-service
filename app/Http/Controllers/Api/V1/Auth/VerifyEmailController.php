<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Auth\ResendVerifyEmail;
use App\Actions\Auth\VerifyEmail;
use App\Commands\Auth\ResendVerifyEmailCommand;
use App\Commands\Auth\VerifyEmailCommand;
use App\Http\Requests\Api\V1\Auth\ResendVerifyEmailRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class VerifyEmailController
{
    public function __construct(
        private readonly VerifyEmail $verify,
        private readonly ResendVerifyEmail $resend
    ) {}

    public function verify(Request $request, string $id, string $hash): JsonResponse
    {
        $this->verify->handle(
            VerifyEmailCommand::fromRequest($request, $id, $hash)
        );

        return JsonResponse::success(
            message: 'Email verified successfully.'
        );
    }

    public function resend(ResendVerifyEmailRequest $request): JsonResponse
    {
        $this->resend->handle(
            ResendVerifyEmailCommand::fromRequest($request)
        );

        return JsonResponse::success(
            code: Response::HTTP_ACCEPTED
        );
    }
}
