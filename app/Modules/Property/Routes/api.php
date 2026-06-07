<?php

use App\Modules\Property\Controllers\PropertyController;
use Illuminate\Support\Facades\Route;

Route::prefix('public')->group(function (): void {
    Route::post('properties', [PropertyController::class, 'storePublic']);
});

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('properties', [PropertyController::class, 'index']);
    Route::get('properties/{property_id}', [PropertyController::class, 'show']);
});

Route::middleware(['auth:sanctum', 'role:employee,agent'])->group(function (): void {
    Route::post('properties', [PropertyController::class, 'store']);
});
