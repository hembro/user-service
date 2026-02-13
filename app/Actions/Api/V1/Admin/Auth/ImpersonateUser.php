<?php

declare(strict_types=1);

namespace App\Actions\Api\V1\Admin\Auth;

use App\DTOs\Api\V1\Auth\TokenDTO;
use App\Enums\Systems;
use App\Events\Admin\UserImpersonated;
use App\Models\User;
use App\Services\Auth\TokenIssuer;
use Illuminate\Support\Facades\Log;

final readonly class ImpersonateUser
{
    public function __construct(
        private TokenIssuer $tokenIssuer,
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

        UserImpersonated::dispatch($target, $admin);

        return $this->tokenIssuer->issueFullToken(
            user: $target,
            system: $system
        );
    }
}
