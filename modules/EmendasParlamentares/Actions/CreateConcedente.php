<?php

namespace Modules\EmendasParlamentares\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\EmendasParlamentares\DTOs\CreateConcedenteDTO;
use Modules\EmendasParlamentares\Models\Concedente;

final readonly class CreateConcedente
{
    public function handle(CreateConcedenteDTO $dto): Concedente
    {
        try {
            DB::beginTransaction();
            $concedente = $dto->toModel(Concedente::class);
            $concedente->save();

            DB::commit();
            Log::info('Concedente criado com sucesso: ' . $concedente->id);
            return $concedente;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao criar concedente: ' . $e->getMessage());
            throw $e;
        }
    }
}