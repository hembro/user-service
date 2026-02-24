<?php

declare(strict_types=1);

namespace App\Commands\Auth;

use App\Enums\SocialProviders;
use App\Enums\Systems;

final readonly class SocialRedirectCommand
{
    public function __construct(
        public SocialProviders $provider,
        public Systems $system
    ) {}
}
