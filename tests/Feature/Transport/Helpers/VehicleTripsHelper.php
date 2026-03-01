<?php

declare(strict_types=1);

namespace Tests\Feature\Transport\Helpers;

use Modules\Transport\Models\VehicleTrip;
use Modules\Transport\Support\VehicleTripStatus;

class VehicleTripsHelper
{
    public static function createTestVehicleTrip(): VehicleTrip
    {
        $vehicleRequest = VehicleRequestsHelper::createTestVehicleRequest();

        $vehicleTrip = new VehicleTrip([
            'vehicle_request_id' => $vehicleRequest->id,
            'status' => fake()->randomElement(VehicleTripStatus::toArray()),
            'started_at' => null,
            'finished_at' => null,
            'occurrences' => [],
        ]);

        $vehicleTrip->save();

        return $vehicleTrip;
    }

    public static function dumbVehicleTripData(): array
    {
        $vehicleRequest = VehicleRequestsHelper::createTestVehicleRequest();

        return [
            'vehicle_request_id' => $vehicleRequest->id,
            'status' => VehicleTripStatus::NOT_STARTED,
            'started_at' => null,
            'finished_at' => null,
            'occurrences' => [],
        ];
    }
}
