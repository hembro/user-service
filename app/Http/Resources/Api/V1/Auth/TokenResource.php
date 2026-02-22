<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read \App\DTOs\Auth\IssuedToken $resource
 */
final class TokenResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'token_type' => $this->resource->tokenType,
            'access_token' => $this->resource->accessToken,
            'expires_in' => $this->resource->expiresIn,
        ];
    }
}
