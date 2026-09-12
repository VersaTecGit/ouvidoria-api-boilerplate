<?php

declare(strict_types=1);

namespace Modules\Ouvidoria\Actions;

use Exception;
use Illuminate\Support\Facades\DB;
use Modules\Ouvidoria\DTOs\UpdateUnitDTO;
use Modules\Ouvidoria\Models\Unit;

final readonly class UpdateUnit
{
    public function __construct(
        private FetchUnit $fetchUnit,
    ) {}

    public function handle(string $uuid, UpdateUnitDTO $dto): Unit
    {
        $unit = $this->fetchUnit->handle($uuid);

        $updateData = $dto->nullableSafeToArray(Unit::nullable());

        DB::beginTransaction();
        try {
            $unit->fill($updateData);
            $unit->save();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            throw $e;
        }

        return $unit;
    }
}
