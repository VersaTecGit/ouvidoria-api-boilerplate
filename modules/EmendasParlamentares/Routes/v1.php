<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\EmendasParlamentares\Controllers\ConcedenteController;
use Modules\EmendasParlamentares\Controllers\EmendaController;
use Modules\EmendasParlamentares\Controllers\EventoFinanceiroController;
use Modules\EmendasParlamentares\Controllers\RecebedorController;

Route::middleware('auth:api')->group(function () {
    Route::prefix('emendas')->group(function () {

        Route::post('/', [EmendaController::class, 'store']);
        Route::get('/', [EmendaController::class, 'index']);

        Route::prefix('eventos-financeiros')->group(function () {
            Route::post('/', [EventoFinanceiroController::class, 'store']);
        });
        
        Route::prefix('concedentes')->group(function () {
            Route::post('/', [ConcedenteController::class, 'store']);
            Route::get('/', [ConcedenteController::class, 'index']);
        });

        Route::prefix('recebedores')->group(function () {
            Route::post('/', [RecebedorController::class, 'store']);
            Route::get('/', [RecebedorController::class, 'index']);
        });

        Route::prefix('modalidades')->group(function () {
            Route::get('/', function () {
                return response()->json([
                    ['id' => 1, 'nome' => 'Obra'],
                    ['id' => 2, 'nome' => 'Serviço'],
                    ['id' => 3, 'nome' => 'Compra'],
                ]);
            });
        });
    });
});
