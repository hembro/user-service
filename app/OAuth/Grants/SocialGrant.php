<?php

declare(strict_types=1);

namespace App\OAuth\Grants;

use App\Models\User;
use Laravel\Passport\Bridge\User as PassportUser;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Http\Message\ServerRequestInterface;

final class SocialGrant extends AbstractInternalGrant
{
    public function getIdentifier(): string
    {
        return 'social';
    }

    protected function validateUser(ServerRequestInterface $request, ClientEntityInterface $client): UserEntityInterface
    {
        $this->ensureInternalSignature($request);

        $userId = $this->getRequestParameter('user_id', $request);
        $user = User::query()->find($userId);

        if (! $user) {
            throw OAuthServerException::invalidCredentials();
        }

        return new PassportUser((string) $user->getAuthIdentifier());
    }
}
