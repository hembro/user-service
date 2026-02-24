<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * @property-read Collection<int, string> $resource
 */
final class RecoveryCodesResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'recovery_codes' => $this->resource,
            'notice' => 'Store these codes in a safe place. They are the only way to recover access if you lose your device.',
        ];
    }
}
