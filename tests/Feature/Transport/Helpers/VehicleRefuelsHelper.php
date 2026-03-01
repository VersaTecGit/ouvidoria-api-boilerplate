<?php

declare(strict_types=1);

namespace Tests\Feature\Transport\Helpers;

use Modules\Transport\Models\Vehicle;
use Modules\Transport\Models\VehicleRefuel;
use Modules\Transport\Support\VehicleFuel;

class VehicleRefuelsHelper
{
    public static function createTestVehicleRefuel(?Vehicle $vehicle = null): VehicleRefuel
    {
        $vehicle ??= VehiclesHelper::createTestVehicle();

        $vehicle = new VehicleRefuel([
            'vehicle_id' => $vehicle->id,
            'refueled_at' => fake()->dateTimeThisYear(),
            'odometer' => fake()->numberBetween(1000, 100000),
            'liters' => fake()->randomFloat(2, 10, 100),
            'price_per_liter' => fake()->randomFloat(2, 1, 10),
            'total_value' => fake()->randomFloat(2, 20, 500),
            'fuel_type' => fake()->randomElement(VehicleFuel::toArray()),
            'station_location' => [
                'address' => fake()->address(),
                'lat' => fake()->latitude(),
                'lng' => fake()->longitude(),
            ],
            'consumption' => fake()->randomFloat(2, 5, 20),
        ]);

        $vehicle->save();

        return $vehicle;
    }

    public static function dumbVehicleRefuelData(?Vehicle $vehicle = null): array
    {
        $vehicle ??= VehiclesHelper::createTestVehicle();

        return [
            'vehicle_id' => $vehicle->id,
            'refueled_at' => '2024-01-15 10:00:00',
            'odometer' => 15000,
            'liters' => 50.5,
            'price_per_liter' => 4.29,
            'total_value' => 216.55,
            'fuel_type' => VehicleFuel::GASOLINE->value,
            'station_location' => [
                'address' => '123 Main St, Anytown, USA',
                'lat' => 40.712776,
                'lng' => -74.005974,
            ],
            'consumption' => 12.5,
        ];
    }
}
