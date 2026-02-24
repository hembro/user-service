<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Users;

use App\Actions\Users\UpdatePassword;
use App\Commands\Users\UpdatePasswordCommand;
use App\Http\Requests\Api\V1\Users\UpdatePasswordRequest;
use App\Traits\HasApiResponse;
use Illuminate\Http\JsonResponse;

final class UpdatePasswordController
{
    use HasApiResponse;

    public function __construct(
        private readonly UpdatePassword $action
    ) {}

    public function __invoke(UpdatePasswordRequest $request): JsonResponse
    {
        $this->action->handle(
            UpdatePasswordCommand::fromRequest($request)
        );

        return $this->success(
            message: 'Password updated successfully. Other active tokens have been terminated.'
        );
    }
}
