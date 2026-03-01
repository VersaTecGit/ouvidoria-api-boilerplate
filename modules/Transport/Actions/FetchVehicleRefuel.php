<?php

declare(strict_types=1);

namespace Modules\Transport\Actions;

use Modules\Transport\Models\VehicleRefuel;

final readonly class FetchVehicleRefuel
{
    public function handle(string $uuid): VehicleRefuel
    {
        return VehicleRefuel::findAllByUuid($uuid);
    }
}
