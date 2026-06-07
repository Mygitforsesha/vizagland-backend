<?php

use App\Modules\Auth\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

require __DIR__.'/../app/Modules/Property/Routes/api.php';

Route::prefix('auth')->group(function (): void {
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });
});
