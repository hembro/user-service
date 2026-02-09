<?php

declare(strict_types=1);

namespace App\DTOs\Api\V1\Admin\Users;

use App\Enums\Systems;
use App\Http\Requests\Api\V1\Admin\Users\RestoreRequest;

final readonly class RestoreUserDTO
{
    public function __construct(
        public Systems $system
    ) {}

    public static function fromRequest(RestoreRequest $request): self
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
