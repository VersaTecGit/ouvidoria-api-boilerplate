<?php

declare(strict_types=1);

namespace Tests\Traits;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\ParallelTesting;
use Illuminate\Support\Facades\URL;
use Modules\Tenant\Models\Tenant;

trait RefreshDatabaseWithTenant
{
    use RefreshDatabase {
        beginDatabaseTransaction as parentBeginDatabaseTransaction;
    }

    protected array $connectionsToTransact = [null, 'tenant'];

    public function beginDatabaseTransaction()
    {
        $this->initializeTenant();

        /*
         * The cache store sits on the central connection, and nothing below
         * wraps it: once tenancy is initialized both entries of
         * $connectionsToTransact resolve to the tenant connection. Anything
         * cached during a test is therefore committed for good -- including
         * the rate limiter counters behind the throttled public routes, which
         * would carry into the next test until the bucket burst and healthy
         * requests started coming back as 429. Clear it with tenancy already
         * up, so the tenant-prefixed keys go with it.
         */
        Cache::flush();

        $this->parentBeginDatabaseTransaction();
    }

    public function initializeTenant(): void
    {
        $tenantId = 'foo';

        $tenant = Tenant::firstOr(function () use ($tenantId) {

            config(['tenancy.database.prefix' => config('tenancy.database.prefix') . ParallelTesting::token() . '_']);

            $dbName = config('tenancy.database.prefix') . $tenantId;

            DB::unprepared("DROP DATABASE IF EXISTS {$dbName}");

            $t = Tenant::create([
                'id' => $tenantId,
                'modules' => ['test'],
            ]);
            if (! $t->domains()->count()) {
                $t->domains()->create(['domain' => $tenantId]);
            }

            return $t;
        });

        tenancy()->initialize($tenant);

        URL::forceRootUrl('http://foo.localhost');
    }
}
