<?php

declare(strict_types=1);

namespace App\Services\Auth;

final readonly class OtpService
{
    public function generate(int $length = 6): string
    {
        $min = 10 ** ($length - 1);
        $max = (10 ** $length) - 1;

        return (string) random_int($min, $max);
    }

    public function hash(string $code): string
    {
        return hash('sha256', $code);
    }

    public function verify(string $storedHash, string $inputCode): bool
    {
        $inputHash = $this->hash($inputCode);

        return hash_equals($storedHash, $inputHash);
    }
}
