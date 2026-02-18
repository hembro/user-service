<?php

declare(strict_types=1);

namespace App\DTOs\Api\V1\Auth;

use App\Enums\Systems;
use App\Http\Requests\Api\V1\Auth\DisableTwoFactorRequest;

final readonly class DisableTwoFactorDTO
{
    public function __construct(
        public Systems $system
    ) {}

    public static function fromRequest(DisableTwoFactorRequest $request): self
    {
        return new self(
            system: $request->attributes->get('system'),
        );
    }
}
