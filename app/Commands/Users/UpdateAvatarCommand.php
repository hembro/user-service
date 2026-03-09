<?php

declare(strict_types=1);

namespace App\Commands\Users;

use App\Http\Requests\Api\V1\Users\UpdateAvatarRequest;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use jeremyaliparo\Foundation\Enums\System;

final readonly class UpdateAvatarCommand
{
    public function __construct(
        public User $user,
        public UploadedFile $file,
        public System $system
    ) {}

    public static function fromRequest(UpdateAvatarRequest $request, User $user): self
    {
        return new self(
            user: $user,
            file: $request->file('avatar'),
            system: $request->attributes->get('system')
        );
    }
}
