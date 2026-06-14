<?php

use App\Modules\User\Controllers\AdminUserController;
use App\Modules\User\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('profile', [ProfileController::class, 'show']);
    Route::put('profile', [ProfileController::class, 'update']);
    Route::patch('profile/password', [ProfileController::class, 'changePassword']);
});

Route::middleware(['auth:sanctum', 'role:admin,super_admin'])->group(function (): void {
    Route::get('admin/users', [AdminUserController::class, 'index']);
    Route::post('admin/users', [AdminUserController::class, 'store']);
    Route::get('admin/users/{user_id}', [AdminUserController::class, 'show']);
    Route::put('admin/users/{user_id}', [AdminUserController::class, 'update']);
    Route::patch('admin/users/{user_id}/status', [AdminUserController::class, 'updateStatus']);
    Route::patch('admin/users/{user_id}/password', [AdminUserController::class, 'resetPassword']);
});
