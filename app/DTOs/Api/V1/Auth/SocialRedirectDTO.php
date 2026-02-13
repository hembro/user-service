<?php

declare(strict_types=1);

namespace App\DTOs\Api\V1\Auth;

use App\Enums\SocialProviders;
use App\Enums\Systems;

final readonly class SocialRedirectDTO
{
    public function __construct(
        public SocialProviders $provider,
        public Systems $system
    ) {}
}
