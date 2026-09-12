<?php

declare(strict_types=1);

namespace Modules\Ouvidoria\Actions;

use Exception;
use Illuminate\Support\Facades\DB;
use Modules\Ouvidoria\DTOs\CreateUnitDTO;
use Modules\Ouvidoria\Models\Unit;

final readonly class CreateUnit
{
    public function handle(CreateUnitDTO $dto): Unit
    {
        DB::beginTransaction();
        try {
            $unit = $dto->toModel(Unit::class);

            $unit->save();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            throw $e;
        }

        return $unit;
    }
}
