<?php

use App\Modules\Auth\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

require __DIR__.'/../app/Modules/Property/Routes/api.php';
require __DIR__.'/../app/Modules/User/Routes/api.php';
require __DIR__.'/../app/Modules/Dashboard/Routes/api.php';
require __DIR__.'/../app/Modules/Lead/Routes/api.php';
require __DIR__.'/../app/Modules/FollowUp/Routes/api.php';
require __DIR__.'/../app/Modules/PublicSite/Routes/api.php';
require __DIR__.'/../app/Modules/PropertyFieldConfiguration/Routes/api.php';
require __DIR__.'/../app/Modules/Notification/Routes/api.php';
require __DIR__.'/../app/Modules/ActivityLog/Routes/api.php';
require __DIR__.'/../app/Modules/Report/Routes/api.php';

Route::prefix('auth')->group(function (): void {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });
});
