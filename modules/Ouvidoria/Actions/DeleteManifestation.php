<?php

declare(strict_types=1);

namespace Modules\Ouvidoria\Actions;

final readonly class DeleteManifestation
{
    public function __construct(
        private FetchManifestation $fetchManifestation,
    ) {}

    public function handle(string $uuid): void
    {
        $manifestation = $this->fetchManifestation->handle($uuid);

        $manifestation->delete();
    }
}
