<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin\Users;

use App\Actions\Admin\Users\CreateUser;
use App\Commands\Admin\Users\CreateUserCommand;
use App\Http\Requests\Api\V1\Admin\Users\StoreRequest as AdminStoreRequest;
use App\Http\Resources\Api\V1\Users\UserResource;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class StoreController
{
    public function __construct(
        private readonly CreateUser $action
    ) {}

    public function __invoke(AdminStoreRequest $request): JsonResponse
    {
        $user = $this->action->handle(
            CreateUserCommand::fromRequest($request),
        );

        return JsonResponse::success(
            data: new UserResource($user),
            message: 'User created successfully',
            code: Response::HTTP_CREATED
        );
    }
}
