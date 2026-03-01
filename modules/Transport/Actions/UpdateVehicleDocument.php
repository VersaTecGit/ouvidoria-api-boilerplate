<?php

declare(strict_types=1);

namespace Modules\Transport\Actions;

use Exception;
use Illuminate\Support\Facades\DB;
use Modules\Transport\DTOs\UpdateVehicleDocumentDTO;
use Modules\Transport\Models\VehicleDocument;

final readonly class UpdateVehicleDocument
{
    public function __construct(
        private FetchVehicleDocument $fetchVehicleDocument,
    ) {}

    public function handle(string $uuid, UpdateVehicleDocumentDTO $dto): VehicleDocument
    {
        $document = $this->fetchVehicleDocument->handle($uuid);
        $updateData = $dto->nullableSafeToArray(VehicleDocument::nullable());

        DB::beginTransaction();
        try {
            $updateData = $this->handleDocuments($document, $updateData);

            if (isset($updateData['attributes'])) {
                $attributesDTO = $document->type->dto()->fromArray($updateData['attributes']);

                $document->attributes = $attributesDTO->toArray();
            }

            $document->fill($updateData);
            $document->save();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            throw $e;
        }

        return $document->fresh()->load('media');
    }

    public function handleDocuments(VehicleDocument $document, array $updateData): array
    {
        $filesToRemove = $updateData['files_to_remove'] ?? [];
        $files = $updateData['files'] ?? [];

        $mediaItems = $document->getMedia('files');
        foreach ($mediaItems as $mediaItem) {
            if (in_array($mediaItem->file_name, $filesToRemove, true)) {
                $mediaItem->delete();
            }
        }

        foreach ($files as $picture) {
            $document->addMediaFromDisk($picture->key, 'central')
                ->usingFileName($picture->uuid . '.' . $picture->extension)
                ->toMediaCollection('files');
        }

        unset($updateData['files_to_remove'], $updateData['files']);

        return $updateData;
    }
}
