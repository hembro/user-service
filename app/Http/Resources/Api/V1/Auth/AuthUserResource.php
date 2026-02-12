<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read \App\Models\User $resource
 */
final class AuthUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'email' => $this->resource->email,
            'status' => $this->resource->status,
            'first_name' => $this->resource->profile?->first_name,
            'last_name' => $this->resource->profile?->last_name,
            'avatar_url' => $this->resource->profile?->avatarUrl,
            'roles' => $this->resource->roles->pluck('name'),
        ];
    }
}
