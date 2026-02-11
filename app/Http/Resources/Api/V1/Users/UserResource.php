<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1\Users;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read \App\Models\User $resource
 */
final class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'status' => $this->resource->status,
            'email' => $this->resource->email,

            $this->mergeWhen($this->resource->relationLoaded('profile'), fn () => [
                'avatar' => $this->resource->profile?->avatarUrl,
                'full_name' => $this->resource->profile?->full_name,
                'title' => $this->resource->profile?->title,
                'first_name' => $this->resource->profile?->first_name,
                'middle_name' => $this->resource->profile?->middle_name,
                'last_name' => $this->resource->profile?->last_name,
                'suffix' => $this->resource->profile?->suffix,
                'sex' => $this->resource->profile?->sex,
                'mobile_number' => $this->resource->profile?->mobile_number,
                'preferences' => $this->resource->profile?->preferences,
            ]),

            'roles' => $this->whenLoaded(
                relationship: 'roles',
                value: fn () => $this->roles->pluck('name')
            ),

            'roles_permissions' => $this->when(
                condition: $this->resource->relationLoaded('roles') && $this->resource->roles->first()?->relationLoaded('permissions'),
                value: fn () => $this->resource->roles
                    ->flatMap(fn ($role) => $role->permissions)
                    ->pluck('name')
                    ->unique()
                    ->values()
                    ->all()
            ),

            'direct_permissions' => $this->whenLoaded(
                relationship: 'permissions',
                value: fn () => $this->permissions->pluck('name')
            ),

            'created_at' => $this->resource->created_at->toIso8601String(),
        ];
    }
}
