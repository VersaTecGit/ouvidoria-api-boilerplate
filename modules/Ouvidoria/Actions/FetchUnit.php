<?php

declare(strict_types=1);

namespace Modules\Ouvidoria\Actions;

use Modules\Ouvidoria\Models\Unit;

final readonly class FetchUnit
{
    public function handle(string $uuid): Unit
    {
        return Unit::withoutGlobalScope('active-units')
            ->where('uuid', $uuid)
            ->firstOrFail();
    }
}
