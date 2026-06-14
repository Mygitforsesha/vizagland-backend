<?php

use App\Modules\Report\Controllers\AdminExportController;
use App\Modules\Report\Controllers\AdminReportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'role:admin,super_admin'])->group(function (): void {
    Route::get('admin/reports/dashboard', [AdminReportController::class, 'dashboard']);
    Route::get('admin/exports', [AdminExportController::class, 'index']);
    Route::post('admin/exports', [AdminExportController::class, 'store']);
    Route::get('admin/exports/{export_job_id}/download', [AdminExportController::class, 'download']);
});
