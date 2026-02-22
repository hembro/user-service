<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\DTOs\Auth\IssuedToken;
use App\Enums\Auth\GrantType;
use App\Enums\Systems;
use App\Models\User;
use Defuse\Crypto\Crypto;
use Exception;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use Nyholm\Psr7\Response as Psr7Response;
use Nyholm\Psr7\ServerRequest as Psr7Request;
use RuntimeException;
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
    public function issueFullToken(User $user, Systems $system): IssuedToken
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

    public function issueRefreshToken(string $refreshToken, Systems $system): IssuedToken
    {
        return $this->issue(
            grantType: GrantType::REFRESH_TOKEN,
            system: $system,
            payload: [
                'refresh_token' => $refreshToken,
            ],
            scopes: null
        );
    }

    public function issueChallengeId(): string
    {
        return Str::random(32);
    }

    public function resolveUserFromRefreshToken(string $token): ?User
    {
        $tokenId = $this->decryptPassportToken($token);

        if (! $tokenId) {
            return null;
        }

        $userId = DB::table('oauth_refresh_tokens')
            ->join('oauth_access_tokens', 'oauth_refresh_tokens.access_token_id', '=', 'oauth_access_tokens.id')
            ->where('oauth_refresh_tokens.id', $tokenId)
            ->where('oauth_refresh_tokens.revoked', false)
            ->where('oauth_access_tokens.revoked', false)
            ->value('oauth_access_tokens.user_id');

        return $userId ? User::find($userId) : null;
    }

    private function decryptPassportToken(string $token): ?string
    {
        try {
            $key = app('encrypter')->getKey();

            $json = Crypto::decryptWithPassword($token, $key);

            $payload = json_decode($json, true);

            return $payload['refresh_token_id'] ?? null;
        } catch (Exception) {
        }

        try {
            $payload = app(Encrypter::class)->decrypt($token);

            return $payload['refresh_token_id'] ?? null;
        } catch (Exception) {
            return null;
        }
    }

    /**
     * The core issuance logic.
     */
    private function issue(GrantType $grantType, Systems $system, array $payload, ?string $scopes = null): IssuedToken
    {
        // 1. Prepare Request Parameters
        $baseParams = [
            ...$payload,
            'grant_type' => $grantType->value,
        ];

        if ($scopes !== null) {
            $baseParams['scope'] = $scopes;
        }

        $requestParams = $this->mergeClientCredentials($system, $baseParams);

        // 2. Construct PSR-7 Request (Internal Call)
        $request = (new Psr7Request('POST', 'oauth/token'))
            ->withParsedBody($requestParams);

        // 3. Execute against OAuth Server
        try {
            $response = $this->server->respondToAccessTokenRequest(
                request: $request,
                response: new Psr7Response()
            );

            return IssuedToken::fromArray(
                json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR)
            );
        } catch (OAuthServerException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new RuntimeException('Critical Token Issuance Failure', 0, $e);
        }
    }

    /**
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
