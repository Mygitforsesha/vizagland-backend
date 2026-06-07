<?php

use App\Modules\Lead\Controllers\LeadController;
use Illuminate\Support\Facades\Route;

Route::prefix('public')->group(function (): void {
    Route::post('leads', [LeadController::class, 'storePublic']);
});

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('leads', [LeadController::class, 'index']);
    Route::get('leads/{lead_id}', [LeadController::class, 'show']);
    Route::put('leads/{lead_id}', [LeadController::class, 'update']);

    Route::middleware('role:admin,super_admin,employee')->group(function (): void {
        Route::post('leads/{lead_id}/assign', [LeadController::class, 'assign']);
    });
});

Route::middleware(['auth:sanctum', 'role:agent'])->group(function (): void {
    Route::post('agent/leads', [LeadController::class, 'storeAgent']);
});

Route::middleware(['auth:sanctum', 'role:employee'])->group(function (): void {
    Route::post('employee/leads', [LeadController::class, 'storeEmployee']);
});
