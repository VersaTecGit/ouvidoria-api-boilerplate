<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Transport\Controllers\VehicleController;
use Modules\Transport\Controllers\VehicleDocumentController;
use Modules\Transport\Controllers\VehicleMaintenanceController;
use Modules\Transport\Controllers\VehicleRefuelController;
use Modules\Transport\Controllers\VehicleRequestController;
use Modules\Transport\Controllers\VehicleTripController;

Route::middleware('auth')->group(function () {
    Route::prefix('vehicles')->group(function () {
        Route::get('/', [VehicleController::class, 'index'])->can('ALL-list-vehicles');
        Route::post('/', [VehicleController::class, 'store'])->can('ALL-create-vehicles');

        Route::prefix('requests')->group(function () {
            Route::get('/', [VehicleRequestController::class, 'index']);
            Route::post('/', [VehicleRequestController::class, 'store'])->can('ALL-create-vehicle-requests');

            Route::prefix('{uuid}')->group(function () {
                Route::get('/', [VehicleRequestController::class, 'show'])->can('ALL-view-vehicle-requests');
                Route::put('/', [VehicleRequestController::class, 'update']);
                Route::delete('/', [VehicleRequestController::class, 'destroy'])->can('ALL-delete-vehicle-requests');
            });
        });

        Route::prefix('trips')->group(function () {
            Route::get('/', [VehicleTripController::class, 'index'])->can('ALL-list-vehicle-trips');

            Route::prefix('{uuid}')->group(function () {
                Route::get('/', [VehicleTripController::class, 'show'])->can('ALL-view-vehicle-trips');
                Route::put('/', [VehicleTripController::class, 'update'])->can('ALL-edit-vehicle-trips');
                Route::delete('/', [VehicleTripController::class, 'destroy'])->can('ALL-delete-vehicle-trips');
            });
        });

        Route::prefix('documents/{uuid}')->group(function () {
            Route::get('/', [VehicleDocumentController::class, 'show'])->can('ALL-view-vehicles');
            Route::put('/', [VehicleDocumentController::class, 'update'])->can('ALL-edit-vehicles');
            Route::delete('/', [VehicleDocumentController::class, 'destroy'])->can('ALL-edit-vehicles');
        });

        Route::prefix('refuels/{uuid}')->group(function () {
            Route::get('/', [VehicleRefuelController::class, 'show'])->can('ALL-view-vehicles');
            Route::put('/', [VehicleRefuelController::class, 'update'])->can('ALL-edit-vehicles');
            Route::delete('/', [VehicleRefuelController::class, 'destroy'])->can('ALL-edit-vehicles');
        });

        Route::prefix('maintenances/{uuid}')->group(function () {
            Route::get('/', [VehicleMaintenanceController::class, 'show'])->can('ALL-view-vehicles');
            Route::put('/', [VehicleMaintenanceController::class, 'update'])->can('ALL-edit-vehicles');
            Route::delete('/', [VehicleMaintenanceController::class, 'destroy'])->can('ALL-edit-vehicles');
        });

        Route::prefix('{uuid}')->group(function () {
            Route::get('/documents', [VehicleDocumentController::class, 'index'])->can('ALL-view-vehicles');
            Route::post('/documents', [VehicleDocumentController::class, 'store'])->can('ALL-edit-vehicles');

            Route::get('/refuels', [VehicleRefuelController::class, 'index'])->can('ALL-view-vehicles');
            Route::post('/refuels', [VehicleRefuelController::class, 'store'])->can('ALL-edit-vehicles');

            Route::get('/maintenances', [VehicleMaintenanceController::class, 'index'])->can('ALL-view-vehicles');
            Route::post('/maintenances', [VehicleMaintenanceController::class, 'store'])->can('ALL-edit-vehicles');

            Route::get('/', [VehicleController::class, 'show'])->can('ALL-view-vehicles');
            Route::put('/', [VehicleController::class, 'update'])->can('ALL-edit-vehicles');
            Route::delete('/', [VehicleController::class, 'destroy'])->can('ALL-delete-vehicles');
        });
    });
});
