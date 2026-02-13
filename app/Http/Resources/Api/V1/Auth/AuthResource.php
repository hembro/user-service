<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1\Auth;

use App\DTOs\Api\V1\Auth\AuthenticationOutcomeDTO;
use App\Enums\Auth\AuthResultStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read AuthenticationOutcomeDTO $resource
 */
final class AuthResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return match ($this->resource->status) {
            AuthResultStatus::AUTHENTICATED => [
                'auth_state' => 'authenticated',
                'access_token' => $this->resource->token->accessToken,
                'token_type' => $this->resource->token->tokenType,
                'expires_in' => $this->resource->token->expiresIn,
                'device_id' => $this->resource->deviceId,
            ],

            AuthResultStatus::REQUIRES_CHALLENGE => [
                'auth_state' => 'pending_challenge',
                'challenge_token' => $this->resource->challengeId,
                'challenge_type' => $this->resource->challengeType->value,
            ],
        };
    }
}
