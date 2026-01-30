<?php

namespace Modules\EmendasParlamentares\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\EmendasParlamentares\DTOs\CreateRecebedorDTO;
use Modules\EmendasParlamentares\Models\Recebedor;

final readonly class CreateRecebedor
{
    public function handle(CreateRecebedorDTO $dto): Recebedor
    {
        try {
            DB::beginTransaction();
            $recebedor = $dto->toModel(Recebedor::class);
            $recebedor->save();

            DB::commit();
            Log::info('Recebedor criado com sucesso: ' . $recebedor->id);
            return $recebedor;

        } catch (\Exception $e) {
            
            DB::rollBack();
            Log::error('Erro ao criar recebedor: ' . $e->getMessage());
            throw $e;
        }
    }
}
