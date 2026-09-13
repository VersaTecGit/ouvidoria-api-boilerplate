<?php

declare(strict_types=1);

namespace Modules\Ouvidoria\Actions;

use Modules\Ouvidoria\Models\Manifestation;

final readonly class FetchManifestation
{
    public function handle(string $uuid): Manifestation
    {
        return Manifestation::query()
            ->with(['destinationAgency', 'unit', 'respondedBy', 'logs.author'])
            ->where('uuid', $uuid)
            ->firstOrFail();
    }
}
