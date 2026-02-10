<?php

declare(strict_types=1);

namespace App\DTOs\Api\V1\Users;

use App\Http\Requests\Api\V1\Users\UpdateAvatarRequest;
use Illuminate\Http\UploadedFile;

final readonly class UpdateAvatarDTO
{
    public function __construct(
        public UploadedFile $file
    ) {}

    public static function fromRequest(UpdateAvatarRequest $request): self
    {
        return new self(
            file: $request->file('avatar')
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            file: $data['avatar']
        );
    }
}
