<?php

namespace Modules\EmendasParlamentares\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\EmendasParlamentares\DTOs\CreateEventoFinanceiroDTO;
use Modules\EmendasParlamentares\Models\EventoFinanceiro;

final readonly class CreateEventoFinanceiro
{
    public function handle(CreateEventoFinanceiroDTO $dto): EventoFinanceiro
    {
        try {
            DB::beginTransaction();
            $eventoFinanceiro = $dto->toModel(EventoFinanceiro::class);
            $eventoFinanceiro->save();

            DB::commit();
            Log::info('Evento Financeiro criado com sucesso: ' . $eventoFinanceiro->id, $dto->toArray());
            return $eventoFinanceiro;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao criar evento financeiro: ' . $e->getMessage(), 
                [
                    'request' => $dto->toArray(),
                    'trace' => $e->getTraceAsString()
                ]);
            throw $e;
        }
    }
}