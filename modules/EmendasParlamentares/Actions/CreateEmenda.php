<?php

namespace Modules\EmendasParlamentares\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\EmendasParlamentares\DTOs\CreateEmendaDTO;
use Modules\EmendasParlamentares\Models\Emenda;

final readonly class CreateEmenda
{
    public function handle(CreateEmendaDTO $dto): Emenda
    {
        try {
            DB::beginTransaction();

            $data = $dto->toArray();

            $agencia = $data['agencia'] ?? null;
            $conta = $data['conta_corrente'] ?? null;
            $eventos = $data['eventos_financeiros'] ?? [];

            unset($data['eventos_financeiros']);
            $emenda = Emenda::create($data);

            if (!empty($eventos)) {
                $eventosFormatados = array_map(function ($evento) use ($agencia, $conta) {
                    return array_merge($evento, [
                        'agencia' => $agencia,
                        'conta_corrente' => $conta
                    ]);
                }, $eventos);

                $emenda->eventosFinanceiros()->createMany($eventosFormatados);
            }
            DB::commit();
            Log::info('Emenda criada com sucesso: ' . $emenda->id);
            return $emenda;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao criar emenda: ' . $e->getMessage());
            throw $e;
        }
    }
}
