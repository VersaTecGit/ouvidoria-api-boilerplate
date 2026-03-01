<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\Auth\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $hasUser = User::count() > 0;

        if (! $hasUser) {
            $operator = User::create([
                'name' => 'Administrador',
                'login' => 'admin',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
            ]);
        }
    }
}
