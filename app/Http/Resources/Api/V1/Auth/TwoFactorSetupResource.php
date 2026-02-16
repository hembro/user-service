<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class TwoFactorSetupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'secret' => $this->resource->secret,
            'qr_code_url' => $this->resource->qrCodeUrl,
            'instructions' => 'Scan the QR code with your authenticator app. Then, enter the 6-digit code to confirm.',
            'expires_in' => 'Secrets do not expire, but this setup flow should be completed immediately.',
        ];
    }
}
