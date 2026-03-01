<?php

declare(strict_types=1);

namespace Modules\Transport\Actions;

final readonly class DeleteVehicle
{
    public function __construct(
        private FetchVehicle $fetchVehicle,
    ) {}

    public function handle(string $uuid): void
    {
        $vehicle = $this->fetchVehicle->handle($uuid);

        $vehicle->delete();
    }
}
