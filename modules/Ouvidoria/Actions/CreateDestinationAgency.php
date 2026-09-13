<?php

declare(strict_types=1);

namespace Modules\Ouvidoria\Actions;

use Exception;
use Illuminate\Support\Facades\DB;
use Modules\Ouvidoria\DTOs\CreateDestinationAgencyDTO;
use Modules\Ouvidoria\Models\DestinationAgency;

final readonly class CreateDestinationAgency
{
    public function handle(CreateDestinationAgencyDTO $dto): DestinationAgency
    {
        DB::beginTransaction();
        try {
            $agency = $dto->toModel(DestinationAgency::class);

            $agency->save();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            throw $e;
        }

        return $agency;
    }
}
