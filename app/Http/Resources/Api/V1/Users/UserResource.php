<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1\Users;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'status' => $this->resource->status,
            'email' => $this->resource->email,

            'full_name' => $this->resource->profile?->full_name,
            'first_name' => $this->resource->profile?->first_name,
            'middle_name' => $this->resource->profile?->middle_name,
            'last_name' => $this->resource->profile?->last_name,
            'suffix' => $this->resource->profile?->suffix,
            'sex' => $this->resource->profile?->sex,
            'mobile_number' => $this->resource->profile?->mobile_number,

            'roles' => $this->resource->getRoleNames(),
            'permissions' => $this->resource->getAllPermissions()->pluck('name'),

            'preferences' => $this->resource->profile?->preferences,
            'created_at' => $this->resource->created_at->toIso8601String(),
        ];
    }
}
