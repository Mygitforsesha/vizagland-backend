<?php

use App\Modules\PublicSite\Controllers\PublicPropertyController;
use App\Modules\PublicSite\Controllers\SupportController;
use Illuminate\Support\Facades\Route;

Route::prefix('public')->group(function (): void {
    Route::get('properties', [PublicPropertyController::class, 'index']);
    Route::get('properties/{property_id}', [PublicPropertyController::class, 'show']);
    Route::get('featured-properties', [PublicPropertyController::class, 'featured']);
    Route::post('support', [SupportController::class, 'store']);
});
