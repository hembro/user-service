<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Api\V1\Auth\VerifyEmail;
use App\Models\User;
use App\Traits\HasApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class VerifyEmailController
{
    use HasApiResponse;

    public function __construct(
        private readonly VerifyEmail $action
    ) {}

    public function __invoke(Request $request, string $id, string $hash): JsonResponse
    {
        $user = User::query()->findOrFail($id);

        $this->action->handle($user, $hash);

        return $this->success(
            message: 'Email verified successfully'
        );
    }
}
