<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Ouvidoria\Controllers\DestinationAgencyController;
use Modules\Ouvidoria\Controllers\ManifestationController;
use Modules\Ouvidoria\Controllers\UnitController;
use Modules\Ouvidoria\Controllers\UnitTypeController;

Route::middleware('auth')->group(function () {
    Route::prefix('unit-types')->group(function () {
        Route::get('/', [UnitTypeController::class, 'index'])->can('ALL-list-units');
    });

    Route::prefix('units')->group(function () {
        Route::get('/', [UnitController::class, 'index'])->can('ALL-list-units');
        Route::post('/', [UnitController::class, 'store'])->can('ALL-create-units');

        Route::prefix('{uuid}')->group(function () {
            Route::get('/', [UnitController::class, 'show'])->can('ALL-view-units');
            Route::put('/', [UnitController::class, 'update'])->can('ALL-edit-units');
            Route::delete('/', [UnitController::class, 'destroy'])->can('ALL-delete-units');
        });
    });

    Route::prefix('destination-agencies')->group(function () {
        Route::get('/', [DestinationAgencyController::class, 'index'])->can('ALL-list-destination-agencies');
        Route::post('/', [DestinationAgencyController::class, 'store'])->can('ALL-create-destination-agencies');

        Route::prefix('{uuid}')->group(function () {
            Route::get('/', [DestinationAgencyController::class, 'show'])->can('ALL-view-destination-agencies');
            Route::put('/', [DestinationAgencyController::class, 'update'])->can('ALL-edit-destination-agencies');
            Route::delete('/', [DestinationAgencyController::class, 'destroy'])->can('ALL-delete-destination-agencies');
        });
    });

    Route::prefix('manifestations')->group(function () {
        Route::get('/', [ManifestationController::class, 'index'])->can('ALL-list-manifestations');
        Route::post('/', [ManifestationController::class, 'store'])->can('ALL-create-manifestations');

        Route::prefix('{uuid}')->group(function () {
            Route::get('/', [ManifestationController::class, 'show'])->can('ALL-view-manifestations');
            Route::put('/', [ManifestationController::class, 'update'])->can('ALL-edit-manifestations');
            Route::delete('/', [ManifestationController::class, 'destroy'])->can('ALL-delete-manifestations');

            // Answering the citizen is a distinct act from editing the triage fields.
            Route::post('respond', [ManifestationController::class, 'respond'])->can('ALL-respond-manifestations');
            Route::post('logs', [ManifestationController::class, 'storeLog'])->can('ALL-respond-manifestations');
        });
    });
});

/*
 * Public routes: consumed by the unauthenticated manifestation form.
 * Serve only active records, through minimal public resources.
 */
Route::prefix('public')->group(function () {
    Route::get('units', [UnitController::class, 'publicIndex'])->middleware('throttle:60,1');
    Route::get('destination-agencies', [DestinationAgencyController::class, 'publicIndex'])->middleware('throttle:60,1');
});
