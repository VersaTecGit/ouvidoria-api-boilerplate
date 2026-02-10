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
            $emenda->fill($dto->toArray());
            $emenda->save();

            DB::commit();
            Log::info('Emenda atualizada com sucesso: ' . $emenda->id, $dto->toArray());
            return $emenda;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar emenda: ' . $e->getMessage());
            throw $e;
        }
    }
}