<?php

declare(strict_types=1);

namespace Modules\Transport\Actions;

use Modules\Transport\Models\VehicleMaintenance;

final readonly class FetchVehicleMaintenance
{
    public function handle(string $uuid): VehicleMaintenance
    {
        return VehicleMaintenance::findAllByUuid($uuid);
    }
}
