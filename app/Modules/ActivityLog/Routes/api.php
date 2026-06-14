<?php

use App\Modules\ActivityLog\Controllers\AdminActivityLogController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'role:admin,super_admin'])->group(function (): void {
    Route::get('admin/activity-logs/export', [AdminActivityLogController::class, 'export']);
    Route::get('admin/activity-logs', [AdminActivityLogController::class, 'index']);
});
