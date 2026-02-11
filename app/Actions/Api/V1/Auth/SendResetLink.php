<?php

declare(strict_types=1);

namespace App\Actions\Api\V1\Auth;

use App\DTOs\Api\V1\Auth\ForgotPasswordDTO;
use Illuminate\Support\Facades\Password;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

final readonly class SendResetLink
{
    public function handle(ForgotPasswordDTO $dto): void
    {
        $status = Password::broker()->sendResetLink(
            credentials: [
                'email' => $dto->email,
            ]
        );

        if ($status !== Password::RESET_LINK_SENT) {
            throw new TooManyRequestsHttpException(
                message: $status
            );
        }
    }
}
