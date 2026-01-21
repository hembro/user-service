<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1\Users;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class UserProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'first_name' => $this->resource->first_name,
            'middle_name' => $this->resource->middle_name,
            'last_name' => $this->resource->last_name,
            'suffix' => $this->resource->suffix,
            'sex' => $this->resource->sex,
            'mobile_number' => $this->resource->mobile_number,
            'preferences' => $this->resource->preferences,
        ];
    }
}
