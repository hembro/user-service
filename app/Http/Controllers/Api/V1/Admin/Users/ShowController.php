<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin\Users;

use App\Actions\Admin\Users\FetchUserForSystem;
use App\Commands\Admin\Users\ShowUserCommand;
use App\Http\Requests\Api\V1\Admin\Users\ShowRequest as AdminShowRequest;
use App\Http\Resources\Api\V1\Users\UserResource;
use App\Models\User;
use App\Traits\HasApiResponse;
use Symfony\Component\HttpFoundation\Response;

final class ShowController
{
    use HasApiResponse;

    public function __construct(
        private readonly FetchUserForSystem $action
    ) {}

    public function __invoke(AdminShowRequest $request, User $user)
    {
        return $this->success(
            data: new UserResource(
                resource: $this->action->handle(
                    ShowUserCommand::fromRequest($request, $user)
                )
            ),
            code: Response::HTTP_OK
        );
    }
}
