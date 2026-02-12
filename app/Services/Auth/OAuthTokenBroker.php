<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\DTOs\Api\V1\Auth\TokenDTO;
use App\Enums\Systems;
use Illuminate\Support\Facades\Config;
use League\OAuth2\Server\AuthorizationServer;
use Nyholm\Psr7\Response as Psr7Response;
use Nyholm\Psr7\ServerRequest as Psr7Request;
use RuntimeException;

final readonly class OAuthTokenBroker
{
    public function __construct(
        private AuthorizationServer $server
    ) {}

    public function issueToken(array $parameters, Systems $system): TokenDTO
    {
        $payload = $this->injectClientCredentials($parameters, $system);

        $request = (new Psr7Request('POST', 'oauth/token'))
            ->withParsedBody($payload);

        $response = $this->server->respondToAccessTokenRequest(
            request: $request,
            response: new Psr7Response()
        );

        $data = json_decode(
            json: (string) $response->getBody(),
            associative: true,
            flags: JSON_THROW_ON_ERROR
        );

        return TokenDTO::fromArray($data);
    }

    private function injectClientCredentials(array $payload, Systems $system): array
    {
        $clients = Config::get('services.passport.frontend_clients');
        $client = $clients[$system->value] ?? null;

        if (! $client || empty($client['client_id']) || empty($client['client_secret'])) {
            throw new RuntimeException("OAuth Client credentials missing for system: {$system->value}");
        }

        return array_merge($payload, [
            'client_id' => $client['client_id'],
            'client_secret' => $client['client_secret'],
        ]);
    }
}
