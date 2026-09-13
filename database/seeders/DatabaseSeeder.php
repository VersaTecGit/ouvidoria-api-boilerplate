<?php

declare(strict_types=1);

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (tenant()) {
            $this->call([
                ThemeSeeder::class,
                UserSeeder::class,
                PermissionSeeder::class,
                UnitSeeder::class,
                DestinationAgencySeeder::class,
            ]);
        } else {
            $this->call([
                TenantSeeder::class,
            ]);
        }
    }
}
