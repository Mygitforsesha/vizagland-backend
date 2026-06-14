<?php

/*
|--------------------------------------------------------------------------
| CORS Configuration
|--------------------------------------------------------------------------
|
| Loads a profile based on APP_ENV:
|   - local, development, testing → cors-profiles/development.php
|   - production → cors-profiles/production.php (set CORS_ALLOWED_ORIGINS in .env)
|
| Bearer token auth (Sanctum): send Authorization: Bearer <token> from allowed origins.
|
*/

$environment = env('APP_ENV', 'production');

$useDevelopmentProfile = in_array($environment, ['local', 'development', 'testing'], true);

return $useDevelopmentProfile
    ? require __DIR__.'/cors-profiles/development.php'
    : require __DIR__.'/cors-profiles/production.php';
