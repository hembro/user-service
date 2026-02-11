<?php

declare(strict_types=1);

namespace App\DTOs\Api\V1\Admin\Users;

use App\Enums\Systems;
use App\Http\Requests\Api\V1\Admin\Users\ResetPasswordRequest;
use SensitiveParameter;

final readonly class ResetPasswordDTO
{
    public function __construct(
        #[SensitiveParameter]
        public string $password,
        public Systems $system
    ) {}

    public static function fromRequest(ResetPasswordRequest $request): self
    {
        return new self(
            password: $request->validated('password'),
            system: $request->attributes->get('system'),
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            password: $data['password'],
            system: Systems::from($data['system']),
        );
    }
}
