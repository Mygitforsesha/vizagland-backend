<?php

use App\Modules\Advertisement\Controllers\AdminAdvertisementController;
use App\Modules\Advertisement\Controllers\PublicAdvertisementController;
use Illuminate\Support\Facades\Route;

Route::prefix('public')->group(function (): void {
    Route::get('advertisements', [PublicAdvertisementController::class, 'index']);
});

Route::middleware(['auth:sanctum', 'role:admin,super_admin'])->group(function (): void {
    Route::get('admin/advertisements', [AdminAdvertisementController::class, 'index']);
    Route::get('admin/advertisements/{advertisement_id}', [AdminAdvertisementController::class, 'show']);
    Route::post('admin/advertisements', [AdminAdvertisementController::class, 'store']);
    Route::put('admin/advertisements/{advertisement_id}', [AdminAdvertisementController::class, 'update']);
    Route::delete('admin/advertisements/{advertisement_id}', [AdminAdvertisementController::class, 'destroy']);
});
