<?php

declare(strict_types=1);

namespace App\Commands\Auth;

use App\Http\Requests\Api\V1\Auth\ResetPasswordRequest;
use jeremyaliparo\Foundation\Enums\System;
use SensitiveParameter;

final readonly class ResetPasswordCommand
{
    public function __construct(
        public string $email,
        public string $token,
        #[SensitiveParameter]
        public string $password,
        public System $system
    ) {}

    public static function fromRequest(ResetPasswordRequest $request): self
    {
        $data = $request->validated();

        return new self(
            email: $data['email'],
            token: $data['token'],
            password: $data['password'],
            system: $request->attributes->get('system')
        );
    }
}
