<?php

declare(strict_types=1);

namespace Modules\Transport\Actions;

use Modules\Transport\DTOs\CreateVehicleRefuelDTO;
use Modules\Transport\Models\VehicleRefuel;

final readonly class CreateVehicleRefuel
{
    public function __construct(
        private FetchVehicle $fetchVehicle
    ) {}

    public function handle(CreateVehicleRefuelDTO $dto, string $uuid): VehicleRefuel
    {
        $vehicle = $this->fetchVehicle->handle($uuid);

        $vehicleRefuel = $dto->toModel(VehicleRefuel::class);
        $vehicleRefuel->vehicle_id = $vehicle->id;
        $vehicleRefuel->save();

        return $vehicleRefuel;
    }
}
