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
            $emenda = $dto->toModel(Emenda::class);
            $emenda->save();

            DB::commit();
            Log::info('Emenda criada com sucesso: ' . $emenda->id, $dto->toArray());
            return $emenda;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao criar emenda: ' . $e->getMessage());
            throw $e;
        }
    }
}