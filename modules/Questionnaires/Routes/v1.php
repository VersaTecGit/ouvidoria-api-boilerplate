<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Questionnaires\Controllers\QuestionnaireController;
use Modules\Questionnaires\Controllers\QuestionnaireResponseController;
use Modules\Questionnaires\Controllers\QuestionnairesGroupController;

Route::middleware('auth')->group(function () {
    Route::prefix('questionnaires')->group(function () {
        Route::prefix('groups')->group(function () {
            Route::get('/', [QuestionnairesGroupController::class, 'index'])->can('ALL-list-questionnaires-groups');
            Route::post('/', [QuestionnairesGroupController::class, 'store'])->can('ALL-create-questionnaires-groups');

            Route::prefix('{uuid}')->group(function () {
                Route::get('/', [QuestionnairesGroupController::class, 'show'])->can('ALL-view-questionnaires-groups');
                Route::put('/', [QuestionnairesGroupController::class, 'update'])->can('ALL-edit-questionnaires-groups');
                Route::delete('/', [QuestionnairesGroupController::class, 'destroy'])->can('ALL-delete-questionnaires-groups');
            });
        });

        Route::prefix('responses')->group(function () {
            Route::get('/', [QuestionnaireResponseController::class, 'index'])->can('ALL-list-questionnaire-responses');

            Route::prefix('{uuid}')->group(function () {
                Route::get('/', [QuestionnaireResponseController::class, 'show'])->can('ALL-view-questionnaire-responses');
            });
        });

        Route::get('/', [QuestionnaireController::class, 'index'])->can('ALL-list-questionnaires');
        Route::post('/', [QuestionnaireController::class, 'store'])->can('ALL-create-questionnaires');

        Route::prefix('{uuid}')->group(function () {
            Route::put('/', [QuestionnaireController::class, 'update'])->can('ALL-edit-questionnaires');
            Route::delete('/', [QuestionnaireController::class, 'destroy'])->can('ALL-delete-questionnaires');
        });
    });
});

Route::post('questionnaires/responses', [QuestionnaireResponseController::class, 'store']);
Route::get('questionnaires/{uuid}', [QuestionnaireController::class, 'show']);
