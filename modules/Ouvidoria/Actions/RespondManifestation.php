<?php

declare(strict_types=1);

namespace Modules\Ouvidoria\Actions;

use Exception;
use Illuminate\Support\Facades\DB;
use Modules\Ouvidoria\DTOs\RespondManifestationDTO;
use Modules\Ouvidoria\Models\Manifestation;
use Modules\Ouvidoria\Support\ManifestationStatus;

/**
 * Saves the attendant's opinion and closes the manifestation as `respondida`.
 *
 * The public log is not optional: the citizen reads the timeline, not this
 * column, so an opinion without its entry would be invisible to the person
 * who filed the manifestation. Both happen in one transaction.
 */
final readonly class RespondManifestation
{
    public function __construct(
        private FetchManifestation $fetchManifestation,
        private CreateManifestationLog $createManifestationLog,
    ) {}

    public function handle(string $uuid, RespondManifestationDTO $dto): Manifestation
    {
        $manifestation = $this->fetchManifestation->handle($uuid);

        DB::beginTransaction();
        try {
            $manifestation->parecer = $dto->parecer;
            $manifestation->responded_by_id = auth()->id();
            $manifestation->responded_at = now();
            $manifestation->status = ManifestationStatus::ANSWERED;

            $manifestation->save();

            $this->createManifestationLog->write(
                $manifestation,
                $dto->parecer,
                isPublic: true,
                status: ManifestationStatus::ANSWERED->value,
            );

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            throw $e;
        }

        return $manifestation->refresh();
    }
}
