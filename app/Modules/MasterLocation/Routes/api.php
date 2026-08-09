<?php

use App\Modules\MasterLocation\Controllers\AdminVillageController;
use App\Modules\MasterLocation\Controllers\MasterLocationController;
use Illuminate\Support\Facades\Route;

Route::prefix('master')->group(function (): void {
    Route::get('locations/search', [MasterLocationController::class, 'search']);
    // FE alias — optional q/search; same response shape as locations search
    Route::get('villages', [MasterLocationController::class, 'villages']);
});

Route::middleware(['auth:sanctum', 'role:admin,super_admin'])->group(function (): void {
    Route::get('admin/villages', [AdminVillageController::class, 'index']);
    Route::post('admin/villages', [AdminVillageController::class, 'store']);
});
