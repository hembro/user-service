<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin\Users;

use App\Actions\Api\V1\Admin\Users\FetchUser;
use App\DTOs\Api\V1\Admin\Users\ShowUserDTO;
use App\Http\Requests\Api\V1\Admin\Users\ShowRequest as AdminShowRequest;
use App\Http\Resources\Api\V1\Users\UserResource;
use App\Models\User;
use App\Traits\HasApiResponse;
use Symfony\Component\HttpFoundation\Response;

final class ShowController
{
    use HasApiResponse;

    public function __construct(
        private readonly FetchUser $action
    ) {}

    public function __invoke(AdminShowRequest $request, User $user)
    {
        return $this->success(
            data: new UserResource(
                $this->action->handle(
                    dto: ShowUserDTO::fromArray($request->validated()),
                    user: $user
                )
            ),
            code: Response::HTTP_OK
        );
    }
}
