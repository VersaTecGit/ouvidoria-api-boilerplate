<?php

declare(strict_types=1);

namespace Modules\Ouvidoria\Actions;

final readonly class DeleteDestinationAgency
{
    public function __construct(
        private FetchDestinationAgency $fetchDestinationAgency,
    ) {}

    public function handle(string $uuid): void
    {
        $agency = $this->fetchDestinationAgency->handle($uuid);

        $agency->delete();
    }
}
