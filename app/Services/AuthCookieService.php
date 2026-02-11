<?php

declare(strict_types=1);

namespace App\Services;

use Symfony\Component\HttpFoundation\Cookie;

final class AuthCookieService
{
    public function make(string $refreshToken): Cookie
    {
        return cookie(
            name: config('cookie.refresh_token.name', 'refresh_token'),
            value: $refreshToken,
            minutes: config('cookie.refresh_token.minutes'),
            path: config('cookie.refresh_token.path'),

            // Allow cross-subdomain transmission (e.g., '.pms.test')
            domain: config('cookie.refresh_token.domain'),

            // STRICT: True if the request is HTTPS, regardless of environment
            secure: request()->secure(),

            // STRICT: True to prevent XSS JavaScript theft
            httpOnly: true,

            raw: false,

            // 'lax' allows subdomains. Use 'none' ONLY if frontend and backend are completely different root domains.
            sameSite: config('cookie.refresh_token.samesite', 'lax')
        );
    }

    public function forget(): Cookie
    {
        return cookie()->forget(
            name: config('cookie.refresh_token.name'),
            path: config('cookie.refresh_token.path')
        );
    }
}
