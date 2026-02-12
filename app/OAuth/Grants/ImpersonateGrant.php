<?php

declare(strict_types=1);

namespace App\OAuth\Grants;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Laravel\Passport\Bridge\User as PassportUser;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Http\Message\ServerRequestInterface;

final class ImpersonateGrant extends AbstractInternalGrant
{
    public function getIdentifier(): string
    {
        return 'impersonate';
    }

    protected function validateUser(ServerRequestInterface $request, ClientEntityInterface $client): UserEntityInterface
    {
        $this->ensureInternalSignature($request);

        $targetUserId = $this->getRequestParameter('target_user_id', $request);
        $adminUserId = $this->getRequestParameter('admin_user_id', $request);

        if (! $targetUserId) {
            throw OAuthServerException::invalidRequest('target_user_id');
        }
        if (! $adminUserId) {
            throw OAuthServerException::invalidRequest('admin_user_id');
        }

        $user = User::query()->find($targetUserId);

        if (! $user) {
            Log::error('Impersonate Gate 2 failed', ['id' => $targetUserId]);
            throw OAuthServerException::invalidCredentials();
        }

        Log::notice('Impersonation session issued', [
            'admin_id' => $adminUserId,
            'target_id' => $targetUserId,
        ]);

        return new PassportUser((string) $user->getAuthIdentifier());
    }
}
