<?php

declare(strict_types=1);

use Illuminate\Auth\AuthenticationException;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\Common\Core\Commands\DeleteBucketTempFiles;
use Modules\Common\Core\Exceptions\ApiException;
use Modules\Common\Core\Exceptions\Exception as CoreException;
use Modules\Common\Logs\Commands\DeleteOldAccessLogs;
use Modules\Tenant\Jobs\InactiveStatusAds;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        // web: __DIR__.'/../routes/web.php',
        // commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'cacheResponse' => \Spatie\ResponseCache\Middlewares\CacheResponse::class,
            'doNotCacheResponse' => \Spatie\ResponseCache\Middlewares\DoNotCacheResponse::class,
            'impersonate.protect' => \Modules\Auth\Middlewares\ProtectFromImpersonation::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/v1*')) {
                return response()->json(['message' => 'Sessão Expirada.'], 401);
            }
        });

        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/v1*')) {
                return response()->json(['message' => $e->getMessage(), 'errors' => $e->errors()], 422);
            }
        });

        $exceptions->render(function (ApiException $e, Request $request) {
            if ($request->is('api/v1*')) {
                return response()->json(['message' => $e->getMessage()], $e->getCode());
            }
        });

        $exceptions->render(function (CoreException $e, Request $request) {
            if ($request->is('api/v1*')) {
                return response()->json(['message' => $e->getMessage()], $e->getCode());
            }
        });

        $exceptions->render(function (Exception $e, Request $request) {
            if ($request->is('api/v1*') && config('app.debug') === false) {
                return response()->json(['message' => 'Erro interno do servidor.'], 500);
            }
        });
    })
    ->withCommands([
        DeleteOldAccessLogs::class,
        DeleteBucketTempFiles::class,
    ])
    ->withSchedule(function (Schedule $schedule) {
        $schedule->call(new InactiveStatusAds())
            ->name('inactive-ads-based-on-end-date')
            ->daily()
            ->onOneServer();

        $schedule->command('app:delete-old-access-logs')->daily();
        $schedule->command('app:delete-bucket-temp-files')->daily();
    })
    ->create();
