<?php

declare(strict_types=1);

namespace Tests\Feature\Ouvidoria\Helpers;

use Modules\Ouvidoria\Models\Unit;
use Modules\Ouvidoria\Models\UnitType;

class UnitsHelper
{
    public static function createTestUnitType(array $overrides = []): UnitType
    {
        $unitType = new UnitType([
            'name' => fake()->words(2, true),
            ...$overrides,
        ]);

        $unitType->save();

        return $unitType;
    }

    public static function createTestUnit(array $overrides = []): Unit
    {
        $unitType = isset($overrides['unit_type_id']) ? null : self::createTestUnitType();

        $unit = new Unit([
            'name' => fake()->company(),
            'description' => fake()->sentence(),
            'active' => true,
            'code' => strtoupper(fake()->unique()->lexify('???')),
            'unit_type_id' => $unitType?->id,
            ...$overrides,
        ]);

        $unit->save();

        return $unit;
    }
}
