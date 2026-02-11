<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\Api\V1\Auth\LoginCredentials;
use App\DTOs\Api\V1\Auth\RefreshTokenDTO;
use App\DTOs\Api\V1\Auth\TokenDTO;
use App\DTOs\Api\V1\Shared\RequestMetadata;
use App\Enums\Systems;
use App\Enums\UserStatus;
use App\Events\Auth\UserLoggedIn;
use App\Events\Auth\UserLoggedOut;
use App\Exceptions\InvalidCredentialsException;
use App\Exceptions\InvalidRefreshTokenException;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use Nyholm\Psr7\Response as Psr7Response;
use Nyholm\Psr7\ServerRequest as Psr7Request;
use Psr\Log\LoggerInterface;
use RuntimeException;

final readonly class AuthService
{
    public function __construct(
        private AuthorizationServer $server,
        private LoggerInterface $logger
    ) {}

    public function login(LoginCredentials $credentials, RequestMetadata $metadata): TokenDTO
    {
        $user = User::query()
            ->where('email', $credentials->email)
            ->first();

        if (! $user || $user->status !== UserStatus::ACTIVE) {
            throw new InvalidCredentialsException(
                message: 'Invalid credentials.'
            );
        }

        try {
            $tokenDto = $this->dispatchRequest(
                payload: [
                    'grant_type' => 'password',
                    'username' => $credentials->email,
                    'password' => $credentials->password,
                    'scope' => $user->roles->implode('name', ' '),
                ],
                system: $credentials->system
            );
        } catch (OAuthServerException $e) {

            $this->logger->error(
                message: $e->getMessage(),
                context: $e->getTrace()
            );

            throw new InvalidCredentialsException(
                message: 'Invalid credentials.'
            );
        }

        UserLoggedIn::dispatch($user, $metadata);

        return $tokenDto;
    }

    public function refresh(RefreshTokenDTO $dto): TokenDTO
    {
        try {
            return $this->dispatchRequest(
                payload: [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $dto->refreshToken,
                    'scope' => '',
                ],
                system: $dto->system
            );
        } catch (OAuthServerException $e) {
            throw new InvalidRefreshTokenException(
                message: 'The refresh token is invalid or has expired.',
                previous: $e
            );
        }
    }

    public function logout(User $user, RequestMetadata $metadata): void
    {
        /** @var \Laravel\Passport\Token|null $accessToken */
        $accessToken = $user->token();

        if (! $accessToken) {
            return;
        }

        $accessToken->revoke();
        $accessToken->refreshToken?->revoke();

        UserLoggedOut::dispatch($user, $metadata);
    }

    private function dispatchRequest(array $payload, Systems $system): TokenDTO
    {
        $clients = Config::get('services.passport.frontend_clients');
        $client = $clients[$system->value] ?? null;

        if (! $client || empty($client['client_id']) || empty($client['client_secret'])) {
            throw new RuntimeException(
                message: 'OAuth Password Client credentials are missing from config.'
            );
        }

        $payload = array_merge($payload, [
            'client_id' => $client['client_id'],
            'client_secret' => $client['client_secret'],
        ]);

        $request = (new Psr7Request('POST', 'oauth/token'))
            ->withParsedBody($payload);

        $response = $this->server->respondToAccessTokenRequest(
            request: $request,
            response: new Psr7Response()
        );

        $data = json_decode((string) $response->getBody(), true);

        return TokenDTO::fromArray($data);
    }
}
