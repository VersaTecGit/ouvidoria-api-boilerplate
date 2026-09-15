<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Ouvidoria\Models\DestinationAgency;

class DestinationAgencySeeder extends Seeder
{
    /**
     * Órgãos oficiais da Prefeitura Municipal de Durandé. Idempotente: chaveado
     * por nome, então rodar `db:seed` de novo nunca duplica linhas. A ordem da
     * lista define o campo `order` (índice * 10) e, com ela, a ordem no combo.
     */
    private const AGENCIES = [
        'Chefe de Gabinete',
        'Controle Interno',
        'Departamento de RH',
        'Manutenção da Iluminação Pública',
        'Ouvidoria',
        'Prefeitura Municipal de Durandé',
        'Procuradoria',
        'Secretaria de Administração',
        'Secretaria Municipal de Agricultura, Indústria, Comércio, Meio Ambiente e desenvolvimento sustentável',
        'Secretaria Municipal de Cultura, Lazer e Turismo',
        'Secretaria Municipal de Educação',
        'Secretaria Municipal de Fazenda, Gestão e Planejamento',
        'Secretaria Municipal de Promoção e Assistência Social',
        'Secretaria de Saúde',
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
