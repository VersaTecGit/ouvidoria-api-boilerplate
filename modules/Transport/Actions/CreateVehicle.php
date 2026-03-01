<?php

declare(strict_types=1);

namespace Modules\Transport\Actions;

use Exception;
use Illuminate\Support\Facades\DB;
use Modules\Transport\DTOs\CreateVehicleDTO;
use Modules\Transport\Models\Vehicle;

final readonly class CreateVehicle
{
    public function handle(CreateVehicleDTO $dto): Vehicle
    {
        DB::beginTransaction();
        try {
            $vehicle = $dto->toModel(Vehicle::class);

            $vehicle->save();

            foreach ($dto->pictures as $picture) {
                $vehicle->addMediaFromDisk($picture->key, 'central')
                    ->usingFileName($picture->uuid . '.' . $picture->extension)
                    ->toMediaCollection('pictures');
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            throw $e;
        }

        return $vehicle;
    }
}
