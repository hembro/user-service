<?php

declare(strict_types=1);

namespace App\DTOs\Api\V1\Admin\Users;

use App\Enums\UserStatus;

final readonly class UpdateUserStatusDTO
{
    public function __construct(
        public UserStatus $status
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            status: UserStatus::from($data['status']),
        );
    }
}
