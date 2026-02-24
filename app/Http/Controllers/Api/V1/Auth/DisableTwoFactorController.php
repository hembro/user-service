<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Auth\DisableTwoFactor;
use App\Commands\Auth\DisableTwoFactorCommand;
use App\Http\Requests\Api\V1\Auth\DisableTwoFactorRequest;
use App\Http\Resources\Api\V1\Auth\AuthUserResource;
use App\Traits\HasApiResponse;
use Illuminate\Http\JsonResponse;

final class DisableTwoFactorController
{
    use HasApiResponse;

    public function __construct(
        private readonly DisableTwoFactor $action
    ) {}

    public function __invoke(DisableTwoFactorRequest $request): JsonResponse
    {
        $this->action->handle(
            DisableTwoFactorCommand::fromRequest($request),
        );

        return $this->success(
            data: new AuthUserResource($request->user()->fresh()),
            message: 'Two-factor authentication has been disabled.'
        );
    }
}
