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

            'full_name' => $this->resource->profile?->full_name,
            'title' => $this->resource->profile?->title,
            'first_name' => $this->resource->profile?->first_name,
            'middle_name' => $this->resource->profile?->middle_name,
            'last_name' => $this->resource->profile?->last_name,
            'suffix' => $this->resource->profile?->suffix,
            'sex' => $this->resource->profile?->sex,
            'mobile_number' => $this->resource->profile?->mobile_number,

            'roles' => $this->whenLoaded(
                relationship: 'roles',
                value: fn () => $this->roles->pluck('name')
            ),

            'direct_permissions' => $this->whenLoaded(
                relationship: 'permissions',
                value: fn () => $this->permissions->pluck('name')
            ),

            'role_permissions' => $this->when(
                condition: $this->hasLoadedRolePermissions(),
                value: fn () => $this->flattenPermissions()
            ),

            'preferences' => $this->resource->profile?->preferences,
            'created_at' => $this->resource->created_at->toIso8601String(),
        ];
    }

    private function hasLoadedRolePermissions(): bool
    {
        return $this->resource->relationLoaded('roles')
            && ($this->resource->roles->isEmpty() || $this->resource->roles->first()->relationLoaded('permissions'));
    }

    private function flattenPermissions(): array
    {
        return $this->resource->roles
            ->flatMap(fn ($role) => $role->permissions)
            ->pluck('name')
            ->unique()
            ->values()
            ->all();
    }
}
