<?php

use App\Modules\MasterLocation\Controllers\MasterLocationController;
use Illuminate\Support\Facades\Route;

Route::prefix('master')->group(function (): void {
    Route::get('locations/search', [MasterLocationController::class, 'search']);
});
