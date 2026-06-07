<?php

use App\Modules\Dashboard\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'role:employee'])->group(function (): void {
    Route::get('employee/dashboard', [DashboardController::class, 'employee']);
});

Route::middleware(['auth:sanctum', 'role:agent'])->group(function (): void {
    Route::get('agent/dashboard', [DashboardController::class, 'agent']);
});

Route::middleware(['auth:sanctum', 'role:admin,super_admin'])->group(function (): void {
    Route::get('admin/dashboard', [DashboardController::class, 'admin']);
});
