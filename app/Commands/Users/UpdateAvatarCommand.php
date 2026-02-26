<?php

declare(strict_types=1);

namespace App\Commands\Users;

use App\DTOs\Shared\RequestMetadata;
use App\Enums\Systems;
use App\Http\Requests\Api\V1\Users\UpdateAvatarRequest;
use App\Models\User;
use Illuminate\Http\UploadedFile;

final readonly class UpdateAvatarCommand
{
    public function __construct(
        public User $user,
        public UploadedFile $file,
        public Systems $system,
        public RequestMetadata $metadata
    ) {}

    public static function fromRequest(UpdateAvatarRequest $request, User $user): self
    {
        return new self(
            user: $user,
            file: $request->file('avatar'),
            system: $request->attributes->get('system'),
            metadata: RequestMetadata::fromRequest($request),
        );
    }
}
