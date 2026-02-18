<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Cookie;

final readonly class AuthCookieService
{
    /**
     * Create the HttpOnly Refresh Token Cookie.
     */
    public function makeRefreshTokenCookie(string $refreshToken): Cookie
    {
        // 1. Fetch Specific Config
        $config = Config::get('cookie.refresh_token') ?? [];

        return $this->makeCookie(
            name: $config['name'],
            value: $refreshToken,
            minutes: $config['minutes']
        );
    }

    /**
     * Create the Long-Lived Device Trust Cookie.
     */
    public function makeDeviceIdCookie(string $deviceId): Cookie
    {
        $config = Config::get('cookie.device_id') ?? [];

        return $this->makeCookie(
            name: $config['name'],
            value: $deviceId,
            minutes: $config['minutes']
        );
    }

    /**
     * Invalidate Refresh Token.
     */
    public function forgetRefreshToken(): Cookie
    {
        return $this->forgetCookie(
            Config::string('cookie.refresh_token.name')
        );
    }

    /**
     * Invalidate Device ID.
     */
    public function forgetDeviceId(): Cookie
    {
        return $this->forgetCookie(
            Config::string('cookie.device_id.name')
        );
    }

    /**
     * The Iron Factory - Independent of Session Config.
     */
    private function makeCookie(string $name, string $value, int $minutes): Cookie
    {
        return cookie(
            name: $name,
            value: $value,
            minutes: $minutes,
            path: '/', // Root path is standard for auth

            // STRICT: Independent of session.domain
            domain: Config::get('cookie.domain'),

            // STRICT: Independent of request()->secure() (proxies can lie)
            // We trust our explicit config first.
            secure: Config::boolean('cookie.secure'),

            httpOnly: true, // Always true for Auth
            raw: false,

            // STRICT: Independent of session.same_site
            sameSite: Config::string('cookie.same_site', 'lax')
        );
    }

    /**
     * Forget Cookie Logic.
     */
    private function forgetCookie(string $name): Cookie
    {
        return cookie()->forget(
            name: $name,
            path: '/',
            domain: Config::get('cookie.domain')
        );
    }
}
