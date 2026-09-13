<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Ouvidoria\Models\DestinationAgency;

class DestinationAgencySeeder extends Seeder
{
    /**
     * Baseline agencies for the manifestation form. Idempotent: keyed by name,
     * so re-running `db:seed` never duplicates rows.
     */
    private const AGENCIES = [
        'Gabinete do Prefeito',
        'Secretaria de Administração',
        'Secretaria de Assistência Social',
        'Secretaria de Educação',
        'Secretaria de Fazenda',
        'Secretaria de Infraestrutura e Obras',
        'Secretaria de Meio Ambiente',
        'Secretaria de Saúde',
        'Secretaria de Segurança Pública',
    ];

    public function run(): void
    {
        foreach (self::AGENCIES as $index => $name) {
            DestinationAgency::withoutGlobalScope('active-destination-agencies')
                ->firstOrCreate(
                    ['name' => $name],
                    [
                        'active' => true,
                        'order' => ($index + 1) * 10,
                    ]
                );
        }
    }
}
