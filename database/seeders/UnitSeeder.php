<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Ouvidoria\Models\Unit;
use Modules\Ouvidoria\Models\UnitType;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $unitType = UnitType::firstOrCreate(['name' => 'Ouvidoria']);

        Unit::withoutGlobalScope('active-units')->firstOrCreate(
            ['code' => 'OUV-001'],
            [
                'name' => 'Ouvidoria',
                'description' => 'Unidade padrão responsável pelo recebimento das manifestações.',
                'active' => true,
                'unit_type_id' => $unitType->id,
            ]
        );
    }
}
