<?php

declare(strict_types=1);

namespace Modules\Transport\Actions;

use Modules\Transport\Models\VehicleTrip;

final readonly class FetchVehicleTrip
{
    public function handle(string $uuid): VehicleTrip
    {
        return VehicleTrip::findAllByUuid($uuid);
    }
}
