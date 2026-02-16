<?php

declare(strict_types=1);

namespace App\DTOs\Api\V1\Auth;

use App\Enums\Systems;
use App\Http\Requests\Api\V1\Auth\DisableTwoFactorRequest;
use SensitiveParameter;

final readonly class DisableTwoFactorDTO
{
    public function __construct(
        #[SensitiveParameter]
        public string $password,
        public Systems $system
    ) {}

    public static function fromRequest(DisableTwoFactorRequest $request): self
    {
        $data = $request->validated();

        return new self(
            password: $data['password'],
            system: $request->attributes->get('system'),
        );
    }
}
