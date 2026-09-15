<?php

declare(strict_types=1);

namespace Modules\Ouvidoria\Actions;

use Exception;
use Illuminate\Support\Facades\DB;
use Modules\Ouvidoria\DTOs\CreateManifestationDTO;
use Modules\Ouvidoria\Models\Manifestation;

final readonly class CreateManifestation
{
    public function handle(CreateManifestationDTO $dto): Manifestation
    {
        DB::beginTransaction();
        try {
            $manifestation = $dto->toModel(Manifestation::class);

            $manifestation->protocol_number = Manifestation::generateProtocolNumber();

            /*
             * Belt and braces over the DTO's `exclude_if`: whatever route the
             * payload took, an anonymous manifestation stores no manifestant.
             */
            if ($manifestation->is_anonymous) {
                $manifestation->manifestant_name = null;
                $manifestation->manifestant_email = null;
                $manifestation->manifestant_phone = null;
                $manifestation->manifestant_document = null;
                $manifestation->manifestant_address = null;
            }

            $manifestation->save();

            foreach ($dto->attachments as $attachment) {
                $manifestation->addMediaFromDisk($attachment->key, 'central')
                    ->usingFileName($attachment->uuid . '.' . $attachment->extension)
                    ->toMediaCollection('attachments');
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            throw $e;
        }

        return $manifestation;
    }
}
