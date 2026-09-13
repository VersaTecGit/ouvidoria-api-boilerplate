<?php

declare(strict_types=1);

namespace Modules\Ouvidoria\Actions;

use Exception;
use Illuminate\Support\Facades\DB;
use Modules\Ouvidoria\DTOs\UpdateManifestationDTO;
use Modules\Ouvidoria\Models\Manifestation;

final readonly class UpdateManifestation
{
    public function __construct(
        private FetchManifestation $fetchManifestation,
    ) {}

    public function handle(string $uuid, UpdateManifestationDTO $dto): Manifestation
    {
        $manifestation = $this->fetchManifestation->handle($uuid);

        $updateData = $dto->nullableSafeToArray(Manifestation::nullable());

        DB::beginTransaction();
        try {
            $manifestation->fill($updateData);
            $manifestation->save();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            throw $e;
        }

        return $manifestation;
    }
}
