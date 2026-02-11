<?php

declare(strict_types=1);

namespace App\DTOs\Api\V1\Admin\Users;

use App\Enums\Systems;
use App\Http\Requests\Api\V1\Admin\Users\DeleteRequest;

final readonly class DeleteUserDTO
{
    public function __construct(
        public Systems $system
    ) {}

    public static function fromRequest(DeleteRequest $request): self
    {
        return new self(
            system: $request->attributes->get('system')
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            system: Systems::from($data['system'])
        );
    }
}
