<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
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
});

/*
 * Public routes: consumed by the unauthenticated manifestation form.
 * Serves only active units through PublicUnitResource.
 */
Route::prefix('public')->group(function () {
    Route::get('units', [UnitController::class, 'publicIndex'])->middleware('throttle:60,1');
});
