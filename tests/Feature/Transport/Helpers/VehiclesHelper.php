<?php

declare(strict_types=1);

namespace Tests\Feature\Transport\Helpers;

use Modules\Transport\Models\Vehicle;
use Modules\Transport\Support\LicenseCategory;
use Modules\Transport\Support\VehicleFuel;
use Modules\Transport\Support\VehicleStatus;
use Modules\Transport\Support\VehicleType;

class VehiclesHelper
{
    public static function createTestVehicle(): Vehicle
    {
        $vehicle = new Vehicle([
            'status' => VehicleStatus::ACTIVE->value,
            'required_license_categories' => [LicenseCategory::B->value],
            'plate' => fake()->regexify('[A-Z]{3}[0-9]{4}'),
            'model' => fake()->word(),
            'brand' => fake()->company(),
            'capacity' => fake()->numberBetween(1, 50),
            'color' => fake()->safeColorName(),
            'fuels' => [VehicleFuel::DIESEL->value, VehicleFuel::GASOLINE->value],
            'manufacture_year' => (string) fake()->year(),
            'renavam' => fake()->unique()->numerify('#########'),
            'chassis_number' => fake()->unique()->regexify('[A-Z0-9]{17}'),
            'type' => VehicleType::CAR->value,
        ]);

        $vehicle->save();

        return $vehicle;
    }

    public static function dumbVehicleData(): array
    {
        return [
            'status' => VehicleStatus::ACTIVE->value,
            'required_license_categories' => [LicenseCategory::B->value],
            'plate' => 'ABC1234',
            'model' => 'Model X',
            'brand' => 'Brand Y',
            'capacity' => 5,
            'color' => 'Red',
            'fuels' => [VehicleFuel::DIESEL->value, VehicleFuel::GASOLINE->value],
            'manufacture_year' => '2020',
            'renavam' => '123456789',
            'chassis_number' => '1HGBH41JXMN109186',
            'type' => VehicleType::CAR->value,
        ];
    }
}
