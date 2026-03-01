<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Tenant\Models\Tenant;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        Tenant::create([
            'id' => 'localhost',
            'name' => 'Test Tenant 1',
            'modules' => [],
        ])
            ->domains()
            ->create([
                'domain' => 'localhost',
            ]);
    }
}
