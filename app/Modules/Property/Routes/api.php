<?php

use App\Modules\Property\Controllers\AdminPropertyActionController;
use App\Modules\Property\Controllers\AdminPropertyController;
use App\Modules\Property\Controllers\PropertyController;
use App\Modules\Property\Controllers\PropertyMediaController;
use App\Modules\Property\Controllers\PropertyReviewController;
use App\Modules\Property\Controllers\PropertySearchController;
use Illuminate\Support\Facades\Route;

Route::post('properties/search', [PropertySearchController::class, 'search']);

Route::prefix('public')->group(function (): void {
    Route::post('properties', [PropertyController::class, 'storePublic']);
});

Route::post('properties', [PropertyController::class, 'store']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('properties', [PropertyController::class, 'index']);
    Route::get('properties/{property_id}', [PropertyController::class, 'show']);
});

Route::middleware(['auth:sanctum', 'role:employee,agent,admin,super_admin'])->group(function (): void {
    Route::post('properties/{property_id}/images', [PropertyMediaController::class, 'uploadImage']);
    Route::post('properties/{property_id}/documents', [PropertyMediaController::class, 'uploadDocument']);
});

Route::middleware(['auth:sanctum', 'role:employee,agent'])->group(function (): void {
    Route::put('properties/{property_id}', [PropertyController::class, 'update']);
    Route::post('properties/{property_id}/submit-for-review', [PropertyController::class, 'submitForReview']);
});

Route::middleware(['auth:sanctum', 'role:employee,agent,admin,super_admin'])->group(function (): void {
    Route::delete('property-images/{property_image_id}', [PropertyMediaController::class, 'deleteImage']);
    Route::delete('property-documents/{property_document_id}', [PropertyMediaController::class, 'deleteDocument']);
});

Route::middleware(['auth:sanctum', 'role:admin,super_admin'])->group(function (): void {
    Route::get('admin/properties', [AdminPropertyController::class, 'index']);
    Route::get('admin/properties/{property_id}', [AdminPropertyController::class, 'show']);
    Route::put('admin/properties/{property_id}', [AdminPropertyController::class, 'update']);
    Route::post('admin/properties/{property_id}/approve', [AdminPropertyActionController::class, 'approve']);
    Route::post('admin/properties/{property_id}/reject', [AdminPropertyActionController::class, 'reject']);
    Route::post('admin/properties/{property_id}/archive', [AdminPropertyActionController::class, 'archive']);
    Route::post('admin/properties/{property_id}/restore', [AdminPropertyActionController::class, 'restore']);
    Route::post('admin/properties/{property_id}/resolve', [AdminPropertyActionController::class, 'resolve']);
    Route::get('property-reviews', [PropertyReviewController::class, 'index']);
    Route::post('properties/{property_id}/request-changes', [PropertyReviewController::class, 'requestChanges']);
});
