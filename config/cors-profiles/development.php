<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Development CORS
    |--------------------------------------------------------------------------
    |
    | Used when APP_ENV is local, development, or testing.
    | Explicit origins only (no wildcard *).
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    'allowed_origins' => [
        'https://vizagland-admin.vercel.app',
        'http://localhost:5173',
        'http://localhost:3000',
    ],

    'allowed_origins_patterns' => [
        '#^https://[\w-]+\.ngrok-free\.dev$#',
        '#^https://[\w-]+\.ngrok\.io$#',
    ],

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
