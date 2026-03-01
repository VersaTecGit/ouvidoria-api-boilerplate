<?php

declare(strict_types=1);

namespace Modules\Transport\Actions;

use Modules\Transport\Models\VehicleRequest;

final readonly class FetchVehicleRequest
{
    public function handle(string $uuid): VehicleRequest
    {
        return VehicleRequest::findAllByUuid($uuid);
    }
}
