<?php

declare(strict_types=1);

namespace Modules\Transport\Actions;

use Modules\Transport\Models\VehicleDocument;

final readonly class FetchVehicleDocument
{
    public function handle(string $uuid): VehicleDocument
    {
        return VehicleDocument::findAllByUuid($uuid);
    }
}
