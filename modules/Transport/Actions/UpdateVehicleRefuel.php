<?php

declare(strict_types=1);

namespace Modules\Transport\Actions;

use Modules\Transport\DTOs\UpdateVehicleRefuelDTO;
use Modules\Transport\Models\VehicleRefuel;

final readonly class UpdateVehicleRefuel
{
    public function __construct(
        private FetchVehicleRefuel $fetchVehicleRefuel,
    ) {}

    public function handle(string $uuid, UpdateVehicleRefuelDTO $dto): VehicleRefuel
    {
        $refuel = $this->fetchVehicleRefuel->handle($uuid);
        $updateData = $dto->nullableSafeToArray(VehicleRefuel::nullable());

        $refuel->fill($updateData);
        $refuel->save();

        return $refuel;
    }
}
