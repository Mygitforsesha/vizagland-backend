<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Production CORS
    |--------------------------------------------------------------------------
    |
    | Origins must be set explicitly via CORS_ALLOWED_ORIGINS in .env.
    | Never use allowed_origins = ['*'] in production.
    |
    | Example:
    | CORS_ALLOWED_ORIGINS=https://vizagland.com,https://www.vizagland.com
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    'allowed_origins' => array_values(array_filter(array_map(
        static fn (string $origin): string => trim($origin),
        explode(',', (string) env('CORS_ALLOWED_ORIGINS', '')),
    ))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => [
        'Authorization',
        'Content-Type',
        'Accept',
        'Origin',
        'X-Requested-With',
        'ngrok-skip-browser-warning',
    ],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
