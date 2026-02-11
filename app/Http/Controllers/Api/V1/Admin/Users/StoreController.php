<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin\Users;

use App\Actions\Api\V1\Admin\Users\CreateUser;
use App\DTOs\Api\V1\Admin\Users\CreateUserDTO;
use App\Http\Requests\Api\V1\Admin\Users\StoreRequest as AdminStoreRequest;
use App\Http\Resources\Api\V1\Users\UserResource;
use App\Traits\HasApiResponse;
use Symfony\Component\HttpFoundation\Response;

final class StoreController
{
    use HasApiResponse;

    public function __construct(
        private readonly CreateUser $action
    ) {}

    public function __invoke(AdminStoreRequest $request)
    {
        $user = $this->action->handle(
            dto: CreateUserDTO::fromRequest($request),
            admin: $request->user()
        );

        return $this->success(
            data: new UserResource($user),
            message: 'User created successfully',
            code: Response::HTTP_CREATED
        );
    }
}
