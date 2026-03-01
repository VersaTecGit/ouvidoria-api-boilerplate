<?php

declare(strict_types=1);

namespace Tests\Feature\Transport\Helpers;

use Modules\Transport\Models\Vehicle;
use Modules\Transport\Models\VehicleMaintenance;
use Modules\Transport\Support\VehicleMaintenanceService;
use Modules\Transport\Support\VehicleMaintenanceStatus;
use Modules\Transport\Support\VehicleMaintenanceType;

class VehicleMaintenancesHelper
{
    public static function createTestVehicleMaintenance(?Vehicle $vehicle = null): VehicleMaintenance
    {
        $vehicle ??= VehiclesHelper::createTestVehicle();

        $vehicle = new VehicleMaintenance([
            'vehicle_id' => $vehicle->id,
            'type' => fake()->randomElement(VehicleMaintenanceType::toArray()),
            'service' => fake()->randomElement(VehicleMaintenanceService::toArray()),
            'other_service' => null,
            'performed_at' => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d H:i:s'),
            'scheduled_at' => null,
            'workshop' => fake()->company(),
            'description' => fake()->paragraph(),
            'cost' => fake()->randomFloat(2, 50, 500),
            'status' => fake()->randomElement(VehicleMaintenanceStatus::toArray()),
        ]);

        $vehicle->save();

        return $vehicle;
    }

    public static function dumbVehicleMaintenanceData(?Vehicle $vehicle = null): array
    {
        $vehicle ??= VehiclesHelper::createTestVehicle();

        return [
            'vehicle_id' => $vehicle->id,
            'type' => VehicleMaintenanceType::CORRECTIVE->value,
            'service' => VehicleMaintenanceService::OIL_CHANGE->value,
            'other_service' => null,
            'performed_at' => '2024-05-01 10:00:00',
            'scheduled_at' => null,
            'workshop' => 'AutoFix Workshop',
            'description' => 'Changed engine oil and filter.',
            'cost' => 120.50,
            'status' => VehicleMaintenanceStatus::DONE->value,
        ];
    }
}
