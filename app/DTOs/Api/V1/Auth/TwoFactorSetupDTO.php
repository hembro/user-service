<?php

declare(strict_types=1);

namespace App\DTOs\Api\V1\Auth;

final readonly class TwoFactorSetupDTO
{
    public function __construct(
        public string $secret,
        public string $qrCodeUrl,
    ) {}

    public function toArray(): array
    {
        return [
            'secret' => $this->secret,
            'qr_code_url' => $this->qrCodeUrl,
            'setup_instructions' => 'Scan the QR code with your authenticator app (Google Authenticator, Authy, etc.) or manually enter the secret key.',
        ];
    }
}
