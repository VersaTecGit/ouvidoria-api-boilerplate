<?php

declare(strict_types=1);

namespace Modules\Ouvidoria\Actions;

use Modules\Ouvidoria\Models\Unit;

final readonly class DeleteUnit
{
    public function __construct(
        private FetchUnit $fetchUnit,
    ) {}

    public function handle(string $uuid): void
    {
        $unit = $this->fetchUnit->handle($uuid);

        $unit->delete();
    }
}
