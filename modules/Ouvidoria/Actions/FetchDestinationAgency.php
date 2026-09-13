<?php

declare(strict_types=1);

namespace Modules\Ouvidoria\Actions;

use Modules\Ouvidoria\Models\DestinationAgency;

final readonly class FetchDestinationAgency
{
    public function handle(string $uuid): DestinationAgency
    {
        return DestinationAgency::withoutGlobalScope('active-destination-agencies')
            ->where('uuid', $uuid)
            ->firstOrFail();
    }
}
