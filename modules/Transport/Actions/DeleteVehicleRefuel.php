<?php

declare(strict_types=1);

namespace Modules\Transport\Actions;

final readonly class DeleteVehicleRefuel
{
    public function __construct(
        private FetchVehicleRefuel $fetchVehicleRefuel,
    ) {}

    public function handle(string $uuid): void
    {
        $refuel = $this->fetchVehicleRefuel->handle($uuid);

        $refuel->delete();
    }
}
