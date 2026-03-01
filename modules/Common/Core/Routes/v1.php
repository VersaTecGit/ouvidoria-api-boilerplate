<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Common\Core\Controllers\NotificationController;
use Modules\Common\Core\Controllers\SignedStorageUrlController;

// Public Routes

// Protected Routes
// Route::middleware('auth:api')->group(function () {
//     // Route::delete('media/{uuid}', DeleteMediaController::class)->can('ALL-delete-media');
// });

Route::post('uploads/signed-storage-url', [SignedStorageUrlController::class, 'store'])->middleware('auth.or.microservice');

Route::middleware('auth')->prefix('notifications')->group(function () {
    Route::get('/', [NotificationController::class, 'index']);
    Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/read-all', [NotificationController::class, 'readAll']);
    Route::delete('/', [NotificationController::class, 'destroyAll']);
    Route::delete('/read', [NotificationController::class, 'destroyRead']);

    Route::prefix('{id}')->group(function () {
        Route::get('/', [NotificationController::class, 'show']);
        Route::post('/read', [NotificationController::class, 'markAsRead']);
        Route::post('/unread', [NotificationController::class, 'markAsUnread']);
        Route::delete('/', [NotificationController::class, 'destroy']);
    });
});
