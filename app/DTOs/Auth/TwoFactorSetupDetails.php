<?php

declare(strict_types=1);

namespace App\DTOs\Auth;

final readonly class TwoFactorSetupDetails
{
    public function __construct(
        public string $secret,
        public string $qrCodeUrl,
    ) {}
}
