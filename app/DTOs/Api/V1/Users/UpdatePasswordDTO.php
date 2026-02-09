<?php

declare(strict_types=1);

namespace App\DTOs\Api\V1\Users;

use App\Http\Requests\Api\V1\Users\UpdatePasswordRequest;
use SensitiveParameter;

final readonly class UpdatePasswordDTO
{
    public function __construct(
        #[SensitiveParameter]
        public string $newPassword
    ) {}

    public static function fromRequest(UpdatePasswordRequest $request): self
    {
        return new self(
            newPassword: $request->validated('password'),
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            newPassword: $data['password'],
        );
    }
}
