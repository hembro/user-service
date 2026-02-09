<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Api\V1\Auth\VerifyEmail;
use App\Models\User;
use App\Notifications\VerifyEmail as VerifyEmailNotification;
use App\Traits\HasApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class VerifyEmailController
{
    use HasApiResponse;

    public function __construct(
        private readonly VerifyEmail $action
    ) {}

    public function verify(string $id, string $hash): JsonResponse
    {
        $user = User::query()->findOrFail($id);

        $this->action->handle($user, $hash);

        return $this->success(
            message: 'Email verified successfully'
        );
    }

    public function resend(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return $this->error(
                message: 'Email already verified',
                code: Response::HTTP_FORBIDDEN
            );
        }

        $user->notify(
            instance: new VerifyEmailNotification
        );

        return $this->success(
            code: Response::HTTP_ACCEPTED
        );
    }
}
