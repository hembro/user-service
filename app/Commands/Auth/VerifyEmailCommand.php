<?php

declare(strict_types=1);

namespace App\Commands\Auth;

use App\DTOs\Shared\RequestMetadata;
use App\Enums\Systems;
use Illuminate\Http\Request;

final readonly class VerifyEmailCommand
{
    public function __construct(
        public string $id,
        public string $hash,
        public Systems $system,
        public RequestMetadata $metadata
    ) {}

    public static function fromRequest(Request $request, string $id, string $hash): self
    {
        return new self(
            id: $id,
            hash: $hash,
            system: $request->attributes->get('system'),
            metadata: RequestMetadata::fromRequest($request)
        );
    }
}
