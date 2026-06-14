<?php

use App\Modules\Notification\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::patch('notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::patch('notifications/{notification_id}/read', [NotificationController::class, 'markAsRead']);
    Route::delete('notifications/{notification_id}', [NotificationController::class, 'destroy']);
});
