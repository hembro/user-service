<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Cookie;

final readonly class AuthCookieService
{
    // Fail-safe defaults in case config is missing entirely
    private const DEFAULT_REFRESH_NAME = 'refresh_token';

    private const DEFAULT_REFRESH_MINUTES = 20160;

    private const DEFAULT_DEVICE_NAME = 'device_id';

    private const DEFAULT_DEVICE_MINUTES = 2628000;

    /**
     * Create the HttpOnly Refresh Token Cookie.
     */
    public function makeRefreshTokenCookie(string $refreshToken): Cookie
    {
        // 1. Fetch Specific Config
        $config = Config::get('cookie.refresh_token') ?? [];

        return $this->makeCookie(
            name: (string) ($config['name'] ?? self::DEFAULT_REFRESH_NAME),
            value: $refreshToken,
            minutes: (int) ($config['minutes'] ?? self::DEFAULT_REFRESH_MINUTES)
        );
    }

    /**
     * Create the Long-Lived Device Trust Cookie.
     */
    public function makeDeviceIdCookie(string $deviceId): Cookie
    {
        $config = Config::get('cookie.device_id') ?? [];

        return $this->makeCookie(
            name: (string) ($config['name'] ?? self::DEFAULT_DEVICE_NAME),
            value: $deviceId,
            minutes: (int) ($config['minutes'] ?? self::DEFAULT_DEVICE_MINUTES)
        );
    }

    /**
     * Invalidate Refresh Token.
     */
    public function forgetRefreshToken(): Cookie
    {
        return $this->forgetCookie(
            (string) Config::get('cookie.refresh_token.name', self::DEFAULT_REFRESH_NAME)
        );
    }

    /**
     * Invalidate Device ID.
     */
    public function forgetDeviceId(): Cookie
    {
        return $this->forgetCookie(
            (string) Config::get('cookie.device_id.name', self::DEFAULT_DEVICE_NAME)
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
            secure: (bool) Config::get('cookie.secure'),

            httpOnly: true, // Always true for Auth
            raw: false,

            // STRICT: Independent of session.same_site
            sameSite: (string) Config::get('cookie.same_site', 'lax')
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
