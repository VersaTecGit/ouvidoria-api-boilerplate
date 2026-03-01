<?php

declare(strict_types=1);

namespace Modules\Common\Core\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Modules\Auth\Models\User;
use Modules\Common\Core\Notifications\ReleaseNotes;
use Modules\Tenant\Models\Tenant;

class SendReleaseNotesNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-release-notes {--release-version=}';

    /**
     * The console command description.
     */
    protected $description = 'Send release notes notifications to all users of all tenants';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $version = $this->option('release-version');

        if (! $version) {
            $this->error('You must provide the --release-version flag. Example: php artisan app:send-release-notes --release-version=1.0.0');

            return self::FAILURE;
        }

        Tenant::all()->each(function (Tenant $tenant) use ($version) {
            tenancy()->initialize($tenant);

            DB::beginTransaction();

            try {
                $users = User::all();

                Notification::send(
                    $users,
                    new ReleaseNotes($version)
                );

                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();

                Log::error('Erro ao enviar release notes', [
                    'tenant_id' => $tenant->id,
                    'version' => $version,
                    'error' => $e->getMessage(),
                ]);
            }
        });

        $this->info("Release notes '{$version}' enviadas para todos os usuários.");

        return self::SUCCESS;
    }
}
