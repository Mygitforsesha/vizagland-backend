<?php

use App\Modules\PropertyImport\Controllers\AdminPropertyImportController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('admin/property-imports', [AdminPropertyImportController::class, 'index']);
Route::middleware('auth:sanctum')->get('admin/property-imports/{property_import_job_id}', [AdminPropertyImportController::class, 'show']);

Route::middleware(['auth:sanctum', 'role:admin,super_admin'])->group(function (): void {
    Route::get('admin/property-import-template', [AdminPropertyImportController::class, 'template']);
    Route::post('admin/property-imports', [AdminPropertyImportController::class, 'store']);
});
