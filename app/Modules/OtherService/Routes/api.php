<?php

use App\Modules\OtherService\Controllers\AdminOtherServiceController;
use App\Modules\OtherService\Controllers\PublicOtherServiceController;
use Illuminate\Support\Facades\Route;

Route::get('other-services', [PublicOtherServiceController::class, 'index']);

Route::prefix('public')->group(function (): void {
    Route::get('other-services', [PublicOtherServiceController::class, 'index']);
});

Route::middleware(['auth:sanctum', 'role:admin,super_admin'])->group(function (): void {
    Route::get('admin/other-services', [AdminOtherServiceController::class, 'index']);
    Route::post('admin/other-services', [AdminOtherServiceController::class, 'store']);
    Route::get('admin/other-services/{other_service_id}', [AdminOtherServiceController::class, 'show']);
    Route::put('admin/other-services/{other_service_id}', [AdminOtherServiceController::class, 'update']);
    Route::patch('admin/other-services/{other_service_id}', [AdminOtherServiceController::class, 'update']);
    Route::patch('admin/other-services/{other_service_id}/status', [AdminOtherServiceController::class, 'updateStatus']);
    Route::delete('admin/other-services/{other_service_id}', [AdminOtherServiceController::class, 'destroy']);
});
