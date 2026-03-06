<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\EmendasParlamentares\Models\ModalidadeTransferencia;

class ModalidadesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $modalidades = [
            ['descricao' => 'Execução direta'],
            ['descricao' => 'Transferência entidade'],
            ['descricao' => 'Transferência governamental'],
            ['descricao' => 'Outros']
        ];

        foreach ($modalidades as $modalidade) {
            ModalidadeTransferencia::where('descricao', $modalidade['descricao'])->firstOrCreate($modalidade);
        }
    }
}