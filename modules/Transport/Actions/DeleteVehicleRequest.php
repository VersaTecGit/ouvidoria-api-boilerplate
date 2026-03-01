<?php

declare(strict_types=1);

namespace Modules\Transport\Actions;

final readonly class DeleteVehicleRequest
{
    public function __construct(
        private FetchVehicleRequest $fetchVehicleRequest,
    ) {}

    public function handle(string $uuid): void
    {
        $vehicleRequest = $this->fetchVehicleRequest->handle($uuid);

        $vehicleRequest->delete();
    }
}
