<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Auth\Controllers\AuthController;
use Modules\Auth\Controllers\ChangeUserAvatarController;
use Modules\Auth\Controllers\ChangeUserPasswordController;
use Modules\Auth\Controllers\ImpersonateController;
use Modules\Auth\Controllers\NewPasswordController;
use Modules\Auth\Controllers\PasswordResetLinkController;
use Modules\Auth\Controllers\PermissionController;
use Modules\Auth\Controllers\RoleController;
use Modules\Auth\Controllers\RoleMemberController;
use Modules\Auth\Controllers\RolePermissionController;
use Modules\Auth\Controllers\SettingsController;
use Modules\Auth\Controllers\TwoFactorController;
use Modules\Auth\Controllers\UserController;
use Modules\Auth\Controllers\UserRoleController;
use Modules\Auth\Controllers\Versa360Controller;

// Public Routes
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('login/2fa', [AuthController::class, 'loginWithTwoFactor']);
    Route::post('refresh', [AuthController::class, 'refresh']);

    Route::post('forgot-password', PasswordResetLinkController::class);
    Route::post('reset-password', NewPasswordController::class);
});

// Protected Routes
Route::middleware('auth')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::prefix('2fa')->group(function () {
            Route::post('enable', [TwoFactorController::class, 'store']);
            Route::post('confirm', [TwoFactorController::class, 'confirm']);
            Route::delete('disable', [TwoFactorController::class, 'destroy']);
            Route::post('regenerate', [TwoFactorController::class, 'regenerate']);
        });

        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'user']);

        Route::prefix('impersonate')->group(function () {
            Route::post('take/{uuid}', [ImpersonateController::class, 'take'])->can('ALL-impersonate');
            Route::delete('leave', [ImpersonateController::class, 'leave']);
            Route::get('info', [ImpersonateController::class, 'info'])->can('ALL-impersonate');
        });

        Route::get('settings', [SettingsController::class, 'index'])->can('ALL-view-auth-settings');
        Route::put('settings', [SettingsController::class, 'update'])->can('ALL-edit-auth-settings');
    });

    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index'])->can('ALL-list-users');
        Route::post('/', [UserController::class, 'store'])->can('ALL-create-users');
        Route::get('/dashboard', [UserController::class, 'dashboard'])->can('ALL-list-users');

        Route::prefix('{uuid}')->group(function () {
            Route::get('/', [UserController::class, 'show'])->can('ALL-view-users');

            Route::put('/', [UserController::class, 'update']);
            Route::delete('/', [UserController::class, 'destroy'])->can('ALL-delete-users');

            Route::put('/password', ChangeUserPasswordController::class)->can('ALL-edit-users-passwords');

            Route::post('avatar', ChangeUserAvatarController::class);

            Route::prefix('roles')->group(function () {
                Route::get('/', [UserRoleController::class, 'index'])->can('ALL-list-user-roles');
                Route::post('/', [UserRoleController::class, 'store'])->can('ALL-edit-user-roles');
            });
        });
    });

    Route::get('permissions', [PermissionController::class, 'index'])->can('ALL-list-permissions');
    Route::get('permissions/modules', [PermissionController::class, 'modules'])->can('ALL-list-permissions');

    Route::prefix('roles')->group(function () {
        Route::get('/', [RoleController::class, 'index'])->can('ALL-list-roles');
        Route::post('/', [RoleController::class, 'store'])->can('ALL-create-roles');

        Route::prefix('{id}')->group(function () {
            Route::get('/', [RoleController::class, 'show'])->can('ALL-view-roles');

            Route::put('/', [RoleController::class, 'update'])->can('ALL-edit-roles');
            Route::delete('/', [RoleController::class, 'destroy'])->can('ALL-delete-roles');

            Route::prefix('permissions')->group(function () {
                Route::get('/', [RolePermissionController::class, 'index'])->can('ALL-list-role-permissions');
                Route::post('/', [RolePermissionController::class, 'store'])->can('ALL-edit-role-permissions');
            });

            Route::prefix('members')->group(function () {
                Route::get('/', [RoleMemberController::class, 'index'])->can('ALL-view-roles');
            });
        });
    });

    Route::prefix('versa360')->group(function () {
        Route::post('/', [Versa360Controller::class, 'store'])->can('ALL-create-versa360-scope-permission-map');
        Route::get('/client', [Versa360Controller::class, 'client'])->can('ALL-get-versa360-client');
        Route::post('redirect', [Versa360Controller::class, 'redirect']);

        Route::prefix('{scope_id}')->group(function () {
            Route::get('/', [Versa360Controller::class, 'show'])->can('ALL-view-versa360-scope-permission-map');
            Route::put('/', [Versa360Controller::class, 'update'])->can('ALL-edit-versa360-scope-permission-map');
            Route::delete('/', [Versa360Controller::class, 'destroy'])->can('ALL-delete-versa360-scope-permission-map');
        });
    });
});
