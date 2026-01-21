<?php

declare(strict_types=1);

namespace App\Services;

use Symfony\Component\HttpFoundation\Cookie;

final class AuthCookieService
{
    public function make(string $refreshToken): Cookie
    {
        return cookie(
            name: config('cookie.refresh_token.name'),
            value: $refreshToken,
            minutes: config('cookie.refresh_token.minutes'),
            path: config('cookie.refresh_token.path'),
            domain: null,
            secure: app()->isProduction(),
            httpOnly: true,
            raw: false,
            sameSite: 'strict'
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
