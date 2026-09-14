<?php

declare(strict_types=1);

namespace Tests\Feature\Ouvidoria\Helpers;

use Modules\Ouvidoria\Models\DestinationAgency;

class DestinationAgenciesHelper
{
    public static function createTestDestinationAgency(array $overrides = []): DestinationAgency
    {
        $agency = new DestinationAgency([
            'name' => 'Secretaria de ' . fake()->words(2, true),
            'active' => true,
            'order' => 0,
            ...$overrides,
        ]);

        $agency->save();

        return $agency;
    }
}
