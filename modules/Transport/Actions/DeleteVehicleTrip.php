<?php

declare(strict_types=1);

namespace Modules\Transport\Actions;

final readonly class DeleteVehicleTrip
{
    public function __construct(
        private FetchVehicleTrip $fetchVehicleTrip,
    ) {}

    public function handle(string $uuid): void
    {
        $vehicleTrip = $this->fetchVehicleTrip->handle($uuid);

        $vehicleTrip->delete();
    }
}
