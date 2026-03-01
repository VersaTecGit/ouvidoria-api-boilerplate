<?php

declare(strict_types=1);

namespace Modules\Transport\Actions;

final readonly class DeleteVehicleDocument
{
    public function __construct(
        private FetchVehicleDocument $fetchVehicleDocument,
    ) {}

    public function handle(string $uuid): void
    {
        $document = $this->fetchVehicleDocument->handle($uuid);

        $document->delete();
    }
}
