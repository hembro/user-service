<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\DTOs\Api\V1\Auth\TokenDTO;
use App\Enums\Auth\ChallengeType;
use App\Enums\Auth\GrantType;
use App\Enums\Systems;
use App\Models\User;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use Nyholm\Psr7\Response as Psr7Response;
use Nyholm\Psr7\ServerRequest as Psr7Request;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final readonly class TokenIssuer
{
    public function __construct(
        private AuthorizationServer $server,
        private array $systemClients,
        private string $internalSignature,
    ) {}

    /**
     * Issues a full access token for a fully authenticated user.
     */
    public function issueFullToken(User $user, Systems $system): TokenDTO
    {
        return $this->issue(
            grantType: GrantType::SYSTEM_VERIFIED,
            system: $system,
            payload: [
                'user_id' => $user->id,
                'internal_signature' => $this->internalSignature,
            ],
            scopes: $user->roles->implode('name', ' ')
        );
    }

    /**
     * Issues a Stateless, Encrypted Challenge Token.
     */
    public function issueChallengeToken(User $user, ChallengeType $type, array $claims = []): string
    {
        $payload = array_merge($claims, [
            'sub' => $user->id,
            'type' => $type->value,
            'exp' => now()->addMinutes(5)->timestamp,
            'scope' => 'auth:challenge',
        ]);

        return Crypt::encrypt($payload);
    }

    /**
     * Decrypts and validates the challenge token.
     */
    public function decryptChallengeToken(string $token): array
    {
        try {
            $payload = Crypt::decrypt($token);
        } catch (DecryptException $e) {
            throw new RuntimeException('Invalid Challenge Token', Response::HTTP_UNAUTHORIZED);
        }

        if (! is_array($payload) || ! isset($payload['exp'], $payload['sub'])) {
            throw new RuntimeException('Malformed Token Payload', Response::HTTP_UNAUTHORIZED);
        }

        if (now()->timestamp > $payload['exp']) {
            throw new RuntimeException('Challenge Token Expired', Response::HTTP_UNAUTHORIZED);
        }

        return $payload;
    }

    public function issueRefreshToken(string $refreshToken, Systems $system): TokenDTO
    {
        return $this->issue(
            grantType: GrantType::REFRESH_TOKEN,
            system: $system,
            payload: [
                'refresh_token' => $refreshToken,
            ],
            scopes: ''
        );
    }

    public function issueChallengeId(): string
    {
        return Str::random(32);
    }

    /**
     * The core issuance logic.
     */
    private function issue(GrantType $grantType, Systems $system, array $payload, string $scopes): TokenDTO
    {
        // 1. Prepare Request Parameters
        $requestParams = $this->mergeClientCredentials(
            system: $system,
            baseParams: [
                ...$payload,
                'grant_type' => $grantType->value,
                'scope' => $scopes,
            ]
        );

        // 2. Construct PSR-7 Request (Internal Call)
        $request = (new Psr7Request('POST', 'oauth/token'))
            ->withParsedBody($requestParams);

        // 3. Execute against OAuth Server
        try {
            $response = $this->server->respondToAccessTokenRequest(
                request: $request,
                response: new Psr7Response()
            );

            return TokenDTO::fromArray(
                json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR)
            );
        } catch (OAuthServerException $e) {
            throw new RuntimeException("OAuth Error: {$e->getErrorType()} - {$e->getMessage()}", 0, $e);
        } catch (Throwable $e) {
            throw new RuntimeException('Critical Token Issuance Failure', 0, $e);
        }
    }

    /**
     * @param  array<string, mixed>  $baseParams
     * @return array<string, mixed>
     */
    private function mergeClientCredentials(Systems $system, array $baseParams): array
    {
        $client = $this->systemClients[$system->value] ?? null;

        if (! $client) {
            throw new RuntimeException("No OAuth client configured for system: {$system->value}");
        }

        return array_merge($baseParams, [
            'client_id' => $client['client_id'],
            'client_secret' => $client['client_secret'],
        ]);
    }
}
