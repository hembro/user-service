<?php

declare(strict_types=1);

namespace App\DTOs\Api\V1\Auth;

use App\Enums\Systems;
use Illuminate\Http\Request;

final readonly class VerifyEmailData
{
    public function __construct(
        public string $id,
        public string $hash,
        public Systems $system
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
