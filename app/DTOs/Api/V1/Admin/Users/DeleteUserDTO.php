<?php

declare(strict_types=1);

namespace App\DTOs\Api\V1\Admin\Users;

use App\Enums\Systems;

final readonly class DeleteUserDTO
{
    public function __construct(
        public Systems $system
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            system: Systems::from($data['system'])
        );
    }
}
