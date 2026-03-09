<?php

declare(strict_types=1);

namespace App\Commands\Auth;

use Illuminate\Http\Request;
use jeremyaliparo\Foundation\Enums\System;

final readonly class VerifyEmailCommand
{
    public function __construct(
        public string $id,
        public string $hash,
        public System $system
    ) {}

    public static function fromRequest(Request $request, string $id, string $hash): self
    {
        return new self(
            id: $id,
            hash: $hash,
            system: $request->attributes->get('system')
        );
    }
}
