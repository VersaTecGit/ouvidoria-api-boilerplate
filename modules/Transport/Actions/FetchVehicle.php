<?php

declare(strict_types=1);

namespace Modules\Transport\Actions;

use Modules\Transport\Models\Vehicle;

final readonly class FetchVehicle
{
    public function handle(string $uuid): Vehicle
    {
        return Vehicle::findAllByUuid($uuid);
    }
}
