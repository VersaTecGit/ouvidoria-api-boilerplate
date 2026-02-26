<?php

namespace Modules\EmendasParlamentares\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\EmendasParlamentares\DTOs\UpdateEmendaDTO;
use Modules\EmendasParlamentares\Models\Emenda;

final readonly class UpdateEmenda
{
    public function handle(int $id, UpdateEmendaDTO $dto): Emenda
    {
        try {
            DB::beginTransaction();

            $emenda = Emenda::findOrFail($id);

            $data = $dto->toArray();
            $eventosData = $data['eventos_financeiros'] ?? [];

            $agencia = $data['agencia'] ?? null;
            $contaCorrente = $data['conta_corrente'] ?? null;

            $emenda->fill($data);
            $emenda->save();

            $idsEnviados = collect($eventosData)->pluck('id')->filter()->toArray();

            $emenda->eventosFinanceiros()
                ->whereNotIn('id', $idsEnviados)
                ->delete();

            foreach ($eventosData as $eventoItem) {
                $eventoItem['agencia'] = $agencia;
                $eventoItem['conta_corrente'] = $contaCorrente;

                if (isset($eventoItem['id']) && $eventoItem['id']) {
                    $eventoExistente = $emenda->eventosFinanceiros()->find($eventoItem['id']);
                    if ($eventoExistente) {
                        $eventoExistente->update($eventoItem);
                    }
                } else {
                    $emenda->eventosFinanceiros()->create($eventoItem);
                }
            }

            DB::commit();
            Log::info('Emenda e Eventos atualizados: ' . $emenda->id);

            return $emenda; // Retorna com os filhos carregados

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar emenda/eventos: ' . $e->getMessage());
            throw $e;
        }
    }
}
