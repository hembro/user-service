<?php

declare(strict_types=1);

return [

    'refresh_token' => [
        'name' => 'refresh_token',
        'minutes' => '43200', // 30 days
        'path' => '/api/v1/auth/refresh',
        'domain' => null,
        'samesite' => 'lax',
    ],

];
