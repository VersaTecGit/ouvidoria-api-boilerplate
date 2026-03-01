<?php

declare(strict_types=1);

namespace Modules\Transport\Actions;

use Exception;
use Illuminate\Support\Facades\DB;
use Modules\Transport\DTOs\UpdateVehicleDTO;
use Modules\Transport\Models\Vehicle;

final readonly class UpdateVehicle
{
    public function __construct(
        private FetchVehicle $fetchVehicle,
    ) {}

    public function handle(string $uuid, UpdateVehicleDTO $dto): Vehicle
    {
        $vehicle = $this->fetchVehicle->handle($uuid);
        $updateData = $dto->nullableSafeToArray(Vehicle::nullable());

        DB::beginTransaction();
        try {
            $updateData = $this->handlePictures($vehicle, $updateData);

            $vehicle->fill($updateData);
            $vehicle->save();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            throw $e;
        }

        return $vehicle->fresh()->load('media');
    }

    public function handlePictures(Vehicle $vehicle, array $updateData): array
    {
        $picturesToRemove = $updateData['pictures_to_remove'] ?? [];
        $pictures = $updateData['pictures'] ?? [];

        $mediaItems = $vehicle->getMedia('pictures');
        foreach ($mediaItems as $mediaItem) {
            if (in_array($mediaItem->file_name, $picturesToRemove, true)) {
                $mediaItem->delete();
            }
        }

        foreach ($pictures as $picture) {
            $vehicle->addMediaFromDisk($picture->key, 'central')
                ->usingFileName($picture->uuid . '.' . $picture->extension)
                ->toMediaCollection('pictures');
        }

        unset($updateData['pictures_to_remove'], $updateData['pictures']);

        return $updateData;
    }
}
