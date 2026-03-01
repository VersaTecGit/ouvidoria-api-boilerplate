<?php

declare(strict_types=1);

namespace Modules\Transport\Actions;

final readonly class DeleteVehicleMaintenance
{
    public function __construct(
        private FetchVehicleMaintenance $fetchVehicleMaintenance,
    ) {}

    public function handle(string $uuid): void
    {
        $maintenance = $this->fetchVehicleMaintenance->handle($uuid);

        $maintenance->delete();
    }
}
