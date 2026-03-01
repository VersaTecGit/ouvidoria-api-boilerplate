<?php

declare(strict_types=1);

namespace Modules\Transport\Actions;

use Exception;
use Illuminate\Support\Facades\DB;
use Modules\Transport\DTOs\CreateVehicleDocumentDTO;
use Modules\Transport\Models\VehicleDocument;

final readonly class CreateVehicleDocument
{
    public function __construct(
        private FetchVehicle $fetchVehicle
    ) {}

    public function handle(CreateVehicleDocumentDTO $dto, string $uuid): VehicleDocument
    {
        $vehicle = $this->fetchVehicle->handle($uuid);

        DB::beginTransaction();
        try {
            $vehicleDocument = $dto->toModel(VehicleDocument::class);
            $vehicleDocument->vehicle_id = $vehicle->id;

            $attributesDTO = $dto->type->dto()->fromArray($dto->attributes);

            $vehicleDocument->attributes = $attributesDTO->toArray();

            $vehicleDocument->save();

            foreach ($dto->files as $file) {
                $vehicleDocument->addMediaFromDisk($file->key, 'central')
                    ->usingFileName($file->uuid . '.' . $file->extension)
                    ->toMediaCollection('files');
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            throw $e;
        }

        return $vehicleDocument;
    }
}
