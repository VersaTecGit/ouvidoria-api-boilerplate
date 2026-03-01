<?php

declare(strict_types=1);

namespace Modules\Transport\Jobs;

use Carbon\CarbonImmutable;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Auth\Models\User;
use Modules\Tenant\Models\Tenant;
use Modules\Transport\Notifications\DriverCnhExpirationNotification;

final class AlertUsersAboutCnhExpiration implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct() {}

    public function __invoke(): void
    {
        $today = CarbonImmutable::today();
        $limitDate = $today->addDays(30);

        Tenant::all()->each(function (Tenant $tenant) use ($today, $limitDate) {
            tenancy()->initialize($tenant);

            DB::beginTransaction();

            try {
                $users = User::query()
                    ->whereNotNull('driver')
                    ->whereBetween('driver->cnh_expiration_date', [
                        $today->toDateString(),
                        $limitDate->toDateString(),
                    ])
                    ->get();

                foreach ($users as $user) {
                    $expiration = CarbonImmutable::parse($user->driver['cnh_expiration_date']);

                    $alreadyNotifiedRecently = $user->notifications()
                        ->where('type', DriverCnhExpirationNotification::class)
                        ->where('created_at', '>=', now()->subDays(15))
                        ->exists();

                    if (! $alreadyNotifiedRecently) {
                        $user->notify(new DriverCnhExpirationNotification($user, $expiration));
                    }
                }

                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();

                Log::error('Erro ao verificar CNHs próximas do vencimento', [
                    'tenant_id' => $tenant->id,
                    'error' => $e->getMessage(),
                ]);
            }
        });
    }
}
