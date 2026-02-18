<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Global Cookie Policy
    |--------------------------------------------------------------------------
    |
    | These settings apply to ALL cookies issued by the Auth Service.
    | In microservices, this usually matches the top-level domain.
    |
    */
    'domain' => env('AUTH_COOKIE_DOMAIN', null),

    // Default to strict HTTPS in production, configurable for local dev
    'secure' => env('AUTH_COOKIE_SECURE', env('APP_ENV') === 'production'),

    // 'lax' is standard. Use 'none' ONLY if API and Frontend are on completely different domains.
    'same_site' => env('AUTH_COOKIE_SAMESITE', 'lax'),

    /*
    |--------------------------------------------------------------------------
    | Specific Cookie Definitions
    |--------------------------------------------------------------------------
    */
    'refresh_token' => [
        'name' => env('AUTH_REFRESH_COOKIE', 'refresh_token'),
        'minutes' => env('AUTH_REFRESH_LIFETIME', 43200), // 30 Days
    ],

    'device_id' => [
        'name' => env('AUTH_DEVICE_COOKIE', 'device_id'),
        'minutes' => env('AUTH_DEVICE_LIFETIME', 2628000), // 5 Years
    ],
];
