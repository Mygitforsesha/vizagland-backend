<?php

use App\Modules\PropertyFieldConfiguration\Controllers\AdminPropertyFieldController;
use App\Modules\PropertyFieldConfiguration\Controllers\PublicMasterDropdownController;
use App\Modules\PropertyFieldConfiguration\Controllers\PublicPropertyFormConfigController;
use Illuminate\Support\Facades\Route;

Route::prefix('public')->group(function (): void {
    Route::get('property-form-config', [PublicPropertyFormConfigController::class, 'show']);
    Route::get('master-dropdowns', [PublicMasterDropdownController::class, 'index']);
});

Route::middleware(['auth:sanctum', 'role:admin,super_admin'])->group(function (): void {
    Route::get('admin/property-fields', [AdminPropertyFieldController::class, 'index']);
    Route::post('admin/property-fields', [AdminPropertyFieldController::class, 'store']);
    Route::put('admin/property-fields/{id}', [AdminPropertyFieldController::class, 'update']);
    Route::delete('admin/property-fields/{id}', [AdminPropertyFieldController::class, 'destroy']);
});
