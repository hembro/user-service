<?php

declare(strict_types=1);

namespace App\Actions\Api\V1\Admin\Auth;

use App\DTOs\Api\V1\Auth\TokenDTO;
use App\Enums\Systems;
use App\Models\User;
use App\Services\Auth\OAuthTokenBroker;
use Illuminate\Support\Facades\Log;

final readonly class ImpersonateUser
{
    public function __construct(
        private OAuthTokenBroker $broker,
    ) {}

    public function handle(User $admin, User $target, Systems $system): TokenDTO
    {
        Log::channel('audit')->warning(
            message: 'User Impersonation Initiated',
            context: [
                'admin_id' => $admin->id,
                'target_id' => $target->id,
                'system' => $system->value,
                'ip' => request()->ip(),
            ]
        );

        return $this->broker->issueSystemVerifiedToken(
            user: $target,
            system: $system,
            scopes: $target->roles->implode('name', ' ')
        );
    }
}
