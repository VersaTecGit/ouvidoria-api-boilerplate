<?php

declare(strict_types=1);

namespace Modules\Transport\Actions;

use Exception;
use Illuminate\Support\Facades\DB;
use Modules\Transport\DTOs\UpdateVehicleMaintenanceDTO;
use Modules\Transport\Models\VehicleMaintenance;

final readonly class UpdateVehicleMaintenance
{
    public function __construct(
        private FetchVehicleMaintenance $fetchVehicleMaintenance,
    ) {}

    public function handle(string $uuid, UpdateVehicleMaintenanceDTO $dto): VehicleMaintenance
    {
        $maintenance = $this->fetchVehicleMaintenance->handle($uuid);
        $updateData = $dto->nullableSafeToArray(VehicleMaintenance::nullable());

        DB::beginTransaction();
        try {
            $updateData = $this->handleFiles($maintenance, $updateData);

            $maintenance->fill($updateData);
            $maintenance->save();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            throw $e;
        }

        return $maintenance->fresh()->load('media');
    }

    public function handleFiles(VehicleMaintenance $maintenance, array $updateData): array
    {
        $filesToRemove = $updateData['files_to_remove'] ?? [];
        $files = $updateData['files'] ?? [];

        $mediaItems = $maintenance->getMedia('files');
        foreach ($mediaItems as $mediaItem) {
            if (in_array($mediaItem->file_name, $filesToRemove, true)) {
                $mediaItem->delete();
            }
        }

        foreach ($files as $picture) {
            $maintenance->addMediaFromDisk($picture->key, 'central')
                ->usingFileName($picture->uuid . '.' . $picture->extension)
                ->toMediaCollection('files');
        }

        unset($updateData['files_to_remove'], $updateData['files']);

        return $updateData;
    }
}
