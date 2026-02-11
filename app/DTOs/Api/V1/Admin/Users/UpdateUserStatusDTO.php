<?php

declare(strict_types=1);

namespace App\DTOs\Api\V1\Admin\Users;

use App\Enums\Systems;
use App\Enums\UserStatus;
use App\Http\Requests\Api\V1\Admin\Users\UpdateStatusRequest;

final readonly class UpdateUserStatusDTO
{
    public function __construct(
        public UserStatus $status,
        public Systems $system
    ) {}

    public static function fromRequest(UpdateStatusRequest $request): self
    {
        $data = $request->validated();

        return new self(
            status: UserStatus::from($data['status']),
            system: $request->attributes->get('system'),
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            status: UserStatus::from($data['status']),
            system: Systems::from($data['system']),
        );
    }
}
