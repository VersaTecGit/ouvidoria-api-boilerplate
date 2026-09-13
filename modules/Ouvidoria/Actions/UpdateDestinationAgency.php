<?php

declare(strict_types=1);

namespace Modules\Ouvidoria\Actions;

use Exception;
use Illuminate\Support\Facades\DB;
use Modules\Ouvidoria\DTOs\UpdateDestinationAgencyDTO;
use Modules\Ouvidoria\Models\DestinationAgency;

final readonly class UpdateDestinationAgency
{
    public function __construct(
        private FetchDestinationAgency $fetchDestinationAgency,
    ) {}

    public function handle(string $uuid, UpdateDestinationAgencyDTO $dto): DestinationAgency
    {
        $agency = $this->fetchDestinationAgency->handle($uuid);

        $updateData = $dto->nullableSafeToArray(DestinationAgency::nullable());

        DB::beginTransaction();
        try {
            $agency->fill($updateData);
            $agency->save();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            throw $e;
        }

        return $agency;
    }
}
