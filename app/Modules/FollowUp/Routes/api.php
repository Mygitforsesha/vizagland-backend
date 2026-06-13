<?php

use App\Modules\FollowUp\Controllers\FollowUpController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('follow-ups', [FollowUpController::class, 'store']);
    Route::get('follow-ups', [FollowUpController::class, 'index']);
    Route::get('follow-ups/{follow_up_id}', [FollowUpController::class, 'show']);
    Route::put('follow-ups/{follow_up_id}', [FollowUpController::class, 'update']);
    Route::get('properties/{property_id}/follow-ups', [FollowUpController::class, 'byProperty']);
    Route::get('leads/{lead_id}/follow-ups', [FollowUpController::class, 'byLead']);
});
